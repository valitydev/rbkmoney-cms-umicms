<?php

use src\Api\Exceptions\WrongDataException;
use src\Api\Exceptions\WrongRequestException;
use src\Api\Invoices\GetInvoiceById\Request\GetInvoiceByIdRequest;
use src\Api\Invoices\GetInvoiceById\Response\GetInvoiceByIdResponse;
use src\Api\Payments\CancelPayment\Request\CancelPaymentRequest;
use src\Api\Payments\CapturePayment\Request\CapturePaymentRequest;
use src\Api\Payments\CreateRefund\Request\CreateRefundRequest;
use src\Api\Payments\PaymentResponse\Flow;
use src\Api\Search\SearchPayments\Request\SearchPaymentsRequest;
use src\Api\Search\SearchPayments\Response\Payment;
use src\Api\Status;
use src\Client\Client;
use src\Client\Sender;
use src\Exceptions\RBKmoneyException;
use src\Exceptions\RequestException;
use src\Helpers\Logger;
use src\Helpers\Paginator;
use UmiCms\Service;

class RBKmoneyAdmin
{

    use baseModuleAdmin;

    /**
     * @var RBKmoney $module
     */
    public $module;

    /**
     * @var array
     */
    private $settings;

    /**
     * @var mysqlConnection
     */
    private $connection;

    public function __construct()
    {
        $this->connection = ConnectionPool::getInstance()->getConnection();

        foreach ($this->connection->queryResult('SELECT * FROM `module_rbkmoney_settings`') as $setting) {
            $this->settings[$setting['code']] = $setting['value'];
        }

        include 'src/autoload.php';
    }

    /**
     * @return void
     *
     * @throws coreException
     */
    public function settings()
    {
        $this->setDataType('list');
        $this->setActionType('view');

        $data = $this->prepareData([], 'settings');
        $data = array_merge($data, [
            'settings' => $this->settings,
        ]);

        $this->setData($data);
        $this->doData();
    }

    /**
     * @return void
     */
    public function save_settings()
    {
        if (empty(getRequest('apiKey'))) {
            $this->redirect($this->module->pre_lang . '/admin/RBKmoney/settings/');
        }

        $params = [
            'apiKey' => getRequest('apiKey'),
            'shopId' => getRequest('shopId'),
            'paymentType' => getRequest('paymentType'),
            'holdExpiration' => getRequest('holdExpiration'),
            'cardHolder' => getRequest('cardHolder'),
            'shadingCvv' => getRequest('shadingCvv'),
            'fiscalization' => getRequest('fiscalization'),
            'saveLogs' => getRequest('saveLogs'),
            'successStatus' => getRequest('successStatus'),
            'cancelStatus' => getRequest('cancelStatus'),
            'holdStatus' => getRequest('holdStatus'),
            'refundStatus' => getRequest('refundStatus'),
        ];

        foreach ($params as $key => $parameter) {
            $param = $this->connection->escape(trim($parameter));

            $this->connection->query("UPDATE `module_rbkmoney_settings` SET `value` = '$param' WHERE `code` = '$key'");
        }

        $this->redirect($this->module->pre_lang . '/admin/RBKmoney/settings/');
    }

    /**
     * @return void
     *
     * @throws coreException
     */
    public function recurrent_items()
    {
        $items = $this->connection->queryResult('SELECT `article` FROM `module_rbkmoney_recurrent_items`');

        $result = '';

        foreach ($items as $item) {
            $result .= $item['article'] . PHP_EOL;
        }

        $this->setDataType('list');
        $this->setActionType('view');

        $data = $this->prepareData([], 'settings');
        $data = array_merge($data, [
            'items' => trim($result),
        ]);

        $this->setData($data);
        $this->doData();
    }

    /**
     * @return void
     */
    public function recurrent_items_save()
    {
        $ids = getRequest('items');

        $ids = array_map(function($value) {
            return trim($value);
        }, explode(PHP_EOL, $ids));

        $this->connection->query('TRUNCATE TABLE `module_rbkmoney_recurrent_items`');

        foreach ($ids as $id) {
            if (!empty($id)) {
                $this->setRecurrent($id);
            }
        }

        $this->redirect($this->module->pre_lang . '/admin/RBKmoney/recurrent_items/');
    }

    /**
     * Сохранение id товаров регулярных платежей
     *
     * @param string $value
     *
     * @return void
     */
    private function setRecurrent($value)
    {
        $param = $this->connection->escape(trim($value));

        $id = $this->connection->queryResult("SELECT `id` FROM `module_rbkmoney_recurrent_items` WHERE `article` = '$param'");

        if ($id->length() === 0) {
            $this->connection->query("INSERT INTO `module_rbkmoney_recurrent_items` (`article`) VALUES ('$param')");
        }
    }

    /**
     * @return array
     */
    private function getRecurrentPayments()
    {
        $result = $this->connection->queryResult("SELECT * FROM `module_rbkmoney_recurrent`");

        if ($result->length() === 0) {
            return [];
        }

        return $result;
    }

    /**
     * @param $id
     *
     * @return array
     */
    protected function getCustomer($id)
    {
        $customer = $this->connection->queryResult("SELECT `user_id`, `status` FROM `module_rbkmoney_recurrent_customers` WHERE `id` = '$id'");

        return $customer->getIterator()->current();
    }

    /**
     * @throws coreException
     */
    public function page_recurrent()
    {
        $this->setDataType('list');
        $this->setActionType('view');

        $data = $this->prepareData([], 'settings');

        $this->setData($data);
        $this->doData();
    }

    /**
     * @return void
     */
    public function getRecurrent()
    {
        $recurrent = [];
        $currentSchema = ((isset($_SERVER['HTTPS']) && preg_match("/^on$/i", $_SERVER['HTTPS'])) ? 'https' : 'http');

        foreach ($this->getRecurrentPayments() as $payment) {
            $customer = $this->getCustomer($payment['recurrent_customer_id']);
            $user = umiObjectsCollection::getInstance()->getObject($customer['user_id']);

            $recurrent[] = [
                'recurrentId' => $payment['id'],
                'buttonName' => getLabel('RBK_MONEY_FORM_BUTTON_DELETE'),
                'userName' => $user->getName(),
                'user' => "$currentSchema://{$_SERVER['HTTP_HOST']}/admin/users/edit/{$customer['user_id']}/",
                'status' => $payment['status'],
                'amount' => $payment['amount'],
                'name' => $payment['name'],
                'date' => $payment['date'],
            ];
        }

        $this->pushToBuffer([
            'result' => [
                'error' => '',
                'recurrent' => $recurrent,
            ]
        ]);
    }

    /**
     * @return void
     *
     * @throws coreException
     */
    public function logs()
    {
        $logger = new Logger();

        $this->setDataType('list');
        $this->setActionType('view');

        $data = $this->prepareData([], 'settings');
        $data = array_merge($data, [
            'logs' => $logger->getLog(),
        ]);

        $this->setData($data);
        $this->doData();
    }

    /**
     * @return void
     */
    public function deleteRecurrent()
    {
        $recurrentId = getRequest('recurrentId');
        $this->connection->queryResult("DELETE FROM `module_rbkmoney_recurrent` WHERE `id` = '$recurrentId'");

        $this->redirect($this->module->pre_lang . '/admin/RBKmoney/page_recurrent/');
    }

    /**
     * @return void
     */
    public function deleteLogs()
    {
        $logger = new Logger();
        $logger->deleteLog();

        $this->redirect($this->module->pre_lang . '/admin/RBKmoney/logs');
    }

    /**
     * @return void
     */
    public function downloadLogs()
    {
        $logger = new Logger();
        $logger->downloadLog();
    }

    /**
     * @throws coreException
     */
    public function page_transactions()
    {
        if (empty(getRequest('dateTo'))) {
            $dateTo = new DateTime();
            $dateTo->setTime(23, 59, 59);
        } else {
            $dateTo = new DateTime(getRequest('dateTo'));
        }
        if (empty(getRequest('dateFrom'))) {
            $dateFrom = new DateTime('today');
        } else {
            $dateFrom = new DateTime(getRequest('dateFrom'));
        }
        $page = (empty(getRequest('page'))) ? 1 : getRequest('page');

        $this->setDataType('list');
        $this->setActionType('view');

        $data = $this->prepareData([], 'settings');
        $data = array_merge($data, [
            'date_to' => $dateTo->format('Y-m-d'),
            'date_from' => $dateFrom->format('Y-m-d'),
            'page' => $page,
        ]);

        $this->setData($data);
        $this->doData();
    }

    /**
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function getTransactions()
    {
        $page = getRequest('page');
        $limit = 10;

        $page = (empty($page) || $page < 1) ? 1 : $page;

        if (!empty(getRequest('date_from'))) {
            $dateFrom = new DateTime();
            $dateFrom->setTimestamp(getRequest('date_from'));
        } else {
            $dateFrom = new DateTime('today');
        }

        if (!empty(getRequest('date_to'))) {
            $dateTo = new DateTime();
            $dateTo->setTimestamp(getRequest('date_to'));
        } else {
            $dateTo = new DateTime();
            $dateTo->setTime(23, 59, 59);
        }

        $today = new DateTime();
        if ($dateFrom->getTimestamp() > $dateTo->getTimestamp() || $dateFrom->getTimestamp() > $today->getTimestamp()) {
            $dateFrom = new DateTime('today');
        }
        if ($dateFrom->getTimestamp() >= $dateTo->getTimestamp()) {
            $dateTo = new DateTime();
            $dateTo = $dateTo->setTime(23, 59, 59);
        }

        $shopId = $this->settings['shopId'];

        try {
            $sender = new Sender(new Client($this->settings['apiKey'], $shopId, RBK_MONEY_API_URL_SETTING));
            $paymentRequest = new SearchPaymentsRequest($shopId, $dateFrom, $dateTo, $limit);
            $paymentRequest->setOffset(($page * $limit) - $limit);

            $payments = $sender->sendSearchPaymentsRequest($paymentRequest);
        } catch (RBKmoneyException $exception) {
            if ($exception->getCode() === RBK_MONEY_HTTP_CODE_UNAUTHORIZED) {
                $error = getLabel('RBK_MONEY_FORBIDDEN_MESSAGE');
            } else {
                $error = $exception->getMessage();
            }

            $this->pushToBuffer([
                'result' => [
                    'error' => $error,
                    'transactions' => '',
                    'pages' => '',
                ]
            ]);
        }

        $statuses = [
            'started' => getLabel('RBK_MONEY_STATUS_STARTED'),
            'processed' => getLabel('RBK_MONEY_STATUS_PROCESSED'),
            'captured' => getLabel('RBK_MONEY_STATUS_CAPTURED'),
            'cancelled' => getLabel('RBK_MONEY_STATUS_CANCELLED'),
            'charged back' => getLabel('RBK_MONEY_STATUS_CHARGED_BACK'),
            'refunded' => getLabel('RBK_MONEY_STATUS_REFUNDED'),
            'failed' => getLabel('RBK_MONEY_STATUS_FAILED'),
        ];

        $transactions = [];

        /**
         * @var $payment Payment
         */
        foreach ($payments->result as $payment) {
            $invoiceRequest = new GetInvoiceByIdRequest($payment->invoiceId);
            $invoice = $sender->sendGetInvoiceByIdRequest($invoiceRequest);
            $metadata = $invoice->metadata->metadata;

            $transactions[] = [
                'orderId' => $metadata['orderId'],
                'invoiceId' => $invoice->id,
                'paymentId' => $payment->id,
                'product' => $invoice->product,
                'flowStatus' => $payment->flow->type,
                'paymentStatus' => $payment->status->getValue(),
                'status' => $statuses[$payment->status->getValue()],
                'amount' => number_format($payment->amount / 100, 2, '.', ''),
                'createdAt' => $payment->createdAt->format(FULL_DATE_FORMAT),
                'button' => $this->getButtons($payment, $invoice, $dateFrom, $dateTo),
            ];
        }

        $pagePath = '?page=(:num)';
        $date = "dateFrom={$dateFrom->format('d.m.Y')}&dateTo={$dateTo->format('d.m.Y')}";

        $paginator = new Paginator($payments->totalCount, $limit, $page, "$pagePath&$date");

        $this->pushToBuffer([
            'result' => [
                'error' => '',
                'transactions' => $transactions,
                'pages' => $this->getPages($paginator),
            ]
        ]);
    }

    /**
     * @param array $array
     */
    private function pushToBuffer($array)
    {
        $buffer = Service::Response()->getCurrentBuffer();
        $buffer->clear();
        $buffer->push(
            json_encode($array)
        );

        $buffer->end();

        exit;
    }

    /**
     * @param Paginator $paginator
     *
     * @return string
     */
    private function getPages(Paginator $paginator)
    {
        $pages = '';

        if (!empty($this->previousUrl)) {
            $pages .= '<td><a href="' . $this->previousUrl.'"><<' . getLabel('RBK_MONEY_PREVIOUS') . '</a></td>';
        }
        foreach ($paginator->getPages() as $page) {
            if ($page['isCurrent'] || '...' === $page['num']) {
                $pages .= '<td>' . $page['num'] . '</td>';
            } else {
                $pages .= '<td><a href="' . $page['url'].'">'. $page['num'] . '</a></td>';
            }
        }
        if (!empty($this->nextUrl)) {
            $pages .= '<td><a href="' . $this->nextUrl.'">' . getLabel('RBK_MONEY_NEXT') . ' >></a></td>';
        }

        return $pages;
    }

    /**
     * @param Payment                $payment
     * @param GetInvoiceByIdResponse $invoice
     * @param DateTime               $dateFrom
     * @param DateTime               $dateTo
     *
     * @return string
     */
    private function getButtons(
        Payment $payment,
        GetInvoiceByIdResponse $invoice,
        DateTime $dateFrom,
        DateTime $dateTo
    ) {
        $statusHold = Flow::HOLD;
        $statusCaptured = Status::CAPTURED;
        $statusProcessed = Status::PROCESSED;
        $button = '';

        if ($statusProcessed === $payment->status->getValue() && $statusHold === $payment->flow->type) {
            $button = '<form action="../transaction_actions">
                                <input type="hidden" name="action" value="capturePayment">
                                <input type="hidden" name="date_from" value="' . $dateFrom->format('d.m.Y') . '">
                                <input type="hidden" name="date_to" value="' . $dateTo->format('d.m.Y') . '">
                                <input type="hidden" name="invoiceId" value="' . $invoice->id . '">
                                <input type="hidden" name="paymentId" value="' . $payment->id . '">
                                <button class="btn color-blue btn-small" type="submit">'
                . getLabel("RBK_MONEY_CONFIRM_PAYMENT") . '</button>
                          </form><br>';
            $button .= '<form action="../transaction_actions">
                                <input type="hidden" name="action" value="cancelPayment">
                                <input type="hidden" name="date_from" value="' . $dateFrom->format('d.m.Y') . '">
                                <input type="hidden" name="date_to" value="' . $dateTo->format('d.m.Y') . '">
                                <input type="hidden" name="invoiceId" value="' . $invoice->id . '">
                                <input type="hidden" name="paymentId" value="' . $payment->id . '">
                                <button class="btn color-blue btn-small" type="submit">'
                . getLabel("RBK_MONEY_CANCEL_PAYMENT") . '</button>
                          </form>';
        } elseif ($statusCaptured === $payment->status->getValue()) {
            $button = '<form action="../transaction_actions">
                                <input type="hidden" name="action" value="createRefund">
                                <input type="hidden" name="date_from" value="' . $dateFrom->format('d.m.Y') . '">
                                <input type="hidden" name="date_to" value="' . $dateTo->format('d.m.Y') . '">
                                <input type="hidden" name="invoiceId" value="' . $invoice->id . '">
                                <input type="hidden" name="paymentId" value="' . $payment->id . '">
                                <button class="btn color-blue btn-small" type="submit">'
                . getLabel("RBK_MONEY_CREATE_PAYMENT_REFUND") . '</button>
                          </form>';
        }

        return $button;
    }

    /**
     * @return void
     *
     * @throws coreException
     */
    public function transaction_actions()
    {
        $invoiceId = getRequest('invoiceId');
        $paymentId = getRequest('paymentId');
        $action = getRequest('action');

        try {
            $this->$action($invoiceId, $paymentId);
        } catch (RBKmoneyException $exception) {
            // Если статус платежа не успел обновиться и пользователь
            // второй раз жмякнул на кнопку, обновляем таблицу с новым статусом
        }

        $this->redirect(
            $this->module->pre_lang .
            '/admin/RBKmoney/page_transactions?dateTo=' .
            getRequest('date_to') .
            '&dateFrom=' .
            getRequest('date_from')
        );
    }

    /**
     * @param $invoiceId
     * @param $paymentId
     *
     * @return void
     *
     * @throws RequestException
     * @throws WrongRequestException
     */
    private function capturePayment($invoiceId, $paymentId)
    {
        $capturePayment = new CapturePaymentRequest(
            $invoiceId,
            $paymentId,
            getLabel('RBK_MONEY_CAPTURED_BY_ADMIN')
        );

        $client = new Client($this->settings['apiKey'], $this->settings['shopId'], RBK_MONEY_API_URL_SETTING);
        $sender = new Sender($client);

        $sender->sendCapturePaymentRequest($capturePayment);
    }

    /**
     * @param $invoiceId
     * @param $paymentId
     *
     * @return void
     *
     * @throws RequestException
     * @throws WrongRequestException
     */
    private function cancelPayment($invoiceId, $paymentId)
    {
        $capturePayment = new CancelPaymentRequest(
            $invoiceId,
            $paymentId,
            getLabel('RBK_MONEY_CAPTURED_BY_ADMIN')
        );

        $client = new Client($this->settings['apiKey'], $this->settings['shopId'], RBK_MONEY_API_URL_SETTING);
        $sender = new Sender($client);

        $sender->sendCancelPaymentRequest($capturePayment);
    }

    /**
     * @param $invoiceId
     * @param $paymentId
     *
     * @return void
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    private function createRefund($invoiceId, $paymentId)
    {
        $capturePayment = new CreateRefundRequest(
            $invoiceId,
            $paymentId,
            getLabel('RBK_MONEY_CAPTURED_BY_ADMIN')
        );

        $client = new Client($this->settings['apiKey'], $this->settings['shopId'], RBK_MONEY_API_URL_SETTING);
        $sender = new Sender($client);

        $sender->sendCreateRefundRequest($capturePayment);
    }

}
