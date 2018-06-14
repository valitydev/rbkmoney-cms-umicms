<?php

use src\Api\Customers\CustomerResponse\Status as CustomerStatus;
use src\Api\Exceptions\WrongDataException;
use src\Api\Exceptions\WrongRequestException;
use src\Api\Payments\CreatePayment\HoldType;
use src\Api\Payments\CreatePayment\Request\CreatePaymentRequest;
use src\Api\Payments\CreatePayment\Request\CustomerPayerRequest;
use src\Api\Payments\CreatePayment\Request\PaymentFlowHoldRequest;
use src\Api\Payments\CreatePayment\Request\PaymentFlowInstantRequest;
use src\Api\Webhooks\InvoicesTopicScope;
use src\Client\Client;
use src\Client\Sender;
use src\Exceptions\RBKmoneyException;
use src\Exceptions\RequestException;
use src\Helpers\Log;
use src\Helpers\Logger;

$callback = new RBKmoneyCallback();

$callback->handle();

class RBKmoneyCallback
{
    /**
     * @var Sender
     */
    private $sender;

    /**
     * @var array
     */
    private $settings = [];

    /**
     * @var mysqlConnection
     */
    private $connection;

    function __construct()
    {
        $this->connection = ConnectionPool::getInstance()->getConnection();

        foreach ($this->connection->queryResult('SELECT * FROM `module_rbkmoney_settings`') as $setting) {
            $this->settings[$setting['code']] = $setting['value'];
        }

        include 'src/autoload.php';

        $this->sender = new Sender(
            new Client(
                $this->settings['apiKey'],
                $this->settings['shopId'],
                RBK_MONEY_API_URL_SETTING
            )
        );
    }

    /**
     * @return void
     */
    public function handle()
    {
        try {
            $signature = $this->getSignatureFromHeader(getenv('HTTP_CONTENT_SIGNATURE'));

            if (empty($signature)) {
                throw new WrongDataException(getLabel('RBK_MONEY_WRONG_SIGNATURE'), RBK_MONEY_HTTP_CODE_FORBIDDEN);
            }

            $signDecode = base64_decode(strtr($signature, '-_,', '+/='));

            $message = file_get_contents('php://input');

            if (empty($message)) {
                throw new WrongDataException(getLabel('RBK_MONEY_WRONG_VALUE') . ' `callback`', RBK_MONEY_HTTP_CODE_BAD_REQUEST);
            }

            if (!$this->verificationSignature($message, $signDecode)) {
                throw new WrongDataException(getLabel('RBK_MONEY_WRONG_SIGNATURE'), RBK_MONEY_HTTP_CODE_FORBIDDEN);
            }

            $callback = json_decode($message);

            if (isset($callback->invoice)) {
                $this->paymentCallback($callback);
            } elseif (isset($callback->customer)) {
                $this->customerCallback($callback->customer);
            }
        } catch (RBKmoneyException $exception) {
            $this->callbackError($exception);
        }

        if ('RBK_MONEY_SHOW_PARAMETER' === $this->settings['saveLogs']) {
            if (!empty($exception)) {
                $responseMessage = $exception->getMessage();
                $responseCode = $exception->getCode();
            } else {
                $responseMessage = '';
                $responseCode = RBK_MONEY_HTTP_CODE_OK;
            }

            $log = new Log(
                RBK_MONEY_CALLBACK_URL,
                'POST',
                json_encode(getallheaders()),
                $responseMessage,
                'Content-Type: application/json'
            );

            $log->setRequestBody(file_get_contents('php://input'))
                ->setResponseCode($responseCode);

            $logger = new Logger();
            $logger->saveLog($log);
        }

        exit;
    }

    /**
     * @param string $data
     * @param string $signature
     *
     * @return bool
     */
    function verificationSignature($data, $signature)
    {
        $publicKeyId = openssl_pkey_get_public($this->settings['publicKey']);

        if (empty($publicKeyId)) {
            return false;
        }

        $verify = openssl_verify($data, $signature, $publicKeyId, OPENSSL_ALGO_SHA256);

        return ($verify == 1);
    }

    /**
     * Возвращает сигнатуру из хедера для верификации
     *
     * @param string $contentSignature
     *
     * @return string
     *
     * @throws WrongDataException
     */
    private function getSignatureFromHeader($contentSignature)
    {
        $signature = preg_replace("/alg=(\S+);\sdigest=/", '', $contentSignature);

        if (empty($signature)) {
            throw new WrongDataException(getLabel('RBK_MONEY_WRONG_SIGNATURE'), RBK_MONEY_HTTP_CODE_FORBIDDEN);
        }

        return $signature;
    }

    /**
     * @param Exception $exception
     */
    private function callbackError(Exception $exception)
    {
        header('Content-Type: application/json', true, $exception->getCode());

        echo json_encode(['message' => $exception->getMessage()], 256);
    }

    /**
     * @param stdClass $customer
     *
     * @return void
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    private function customerCallback(stdClass $customer)
    {
        $this->updateCustomerStatus($customer);

        if ($holdType = ('RBK_MONEY_PAYMENT_TYPE_HOLD' === $this->settings['paymentType'])) {
            $paymentFlow = new PaymentFlowHoldRequest($this->getHoldType());
        } else {
            $paymentFlow = new PaymentFlowInstantRequest();
        }

        $payRequest = new CreatePaymentRequest(
            $paymentFlow,
            new CustomerPayerRequest($customer->id),
            $customer->metadata->firstInvoiceId
        );

        $this->sender->sendCreatePaymentRequest($payRequest);
    }

    /**
     * @return HoldType
     *
     * @throws WrongDataException
     */
    private function getHoldType()
    {
        $holdType = ('RBK_MONEY_EXPIRATION_PAYER' === $this->settings['holdExpiration'])
            ? HoldType::CANCEL : HoldType::CAPTURE;

        return new HoldType($holdType);
    }

    /**
     * @param stdClass $customer
     *
     * @return void
     *
     * @throws WrongDataException
     */
    private function updateCustomerStatus(stdClass $customer)
    {
        $status = new CustomerStatus($customer->status);

        $this->connection->query("UPDATE `module_rbkmoney_recurrent_customers` SET `status` = '{$status->getValue()}'
            WHERE `user_id` = '{$customer->metadata->userId}'");
    }

    /**
     * @param stdClass $callback
     */
    private function paymentCallback(stdClass $callback)
    {
        if (isset($callback->invoice->metadata->orderId)) {
            $this->includePaymentClasses();
            $order = order::get($callback->invoice->metadata->orderId);

            if (isset($callback->eventType) && !empty($order)) {
                $type = $callback->eventType;

                switch ($type) {
                    case InvoicesTopicScope::INVOICE_PAID:
                    case InvoicesTopicScope::PAYMENT_CAPTURED:
                        $this->updateOrderStatus($this->settings['successStatus'], $order);
                        include 'src/Customers.php';

                        $customers = new Customers($this->sender);
                        $customers->setRecurrentReadyStatuses($order);
                        break;
                    case InvoicesTopicScope::INVOICE_CANCELLED:
                    case InvoicesTopicScope::PAYMENT_CANCELLED:
                        $this->updateOrderStatus($this->settings['cancelStatus'], $order);
                        break;
                    case InvoicesTopicScope::PAYMENT_REFUNDED:
                        $this->updateOrderStatus($this->settings['refundStatus'], $order);
                        break;
                    case InvoicesTopicScope::PAYMENT_PROCESSED:
                        $this->updateOrderStatus($this->settings['holdStatus'], $order);
                        break;
                }
            }
        }
    }

    /**
     * @param string $statusCode
     * @param order  $order
     */
    private function updateOrderStatus($statusCode, order $order)
    {
        $statusId = order::getStatusByCode($statusCode);

        $order->setValue('status_id', $statusId);
        $order->commit();
    }

    /**
     * @return void
     */
    private function includePaymentClasses()
    {
        require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/components/emarket/autoload.php';

        // Сначала нужно подключить интерфейсы
        foreach ($classes as $key => $class) {
            $filePath = current($class);

            if (preg_match('/\/i\w+.php/', $filePath)) {
                require_once $filePath;
                unset($classes[$key]);
            }
        }

        foreach ($classes as $class) {
            require_once current($class);
        }

        require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/components/emarket/classes/discounts/discounts/orderDiscount.php';
    }

}
