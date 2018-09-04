<?php

use src\Api\Exceptions\WrongDataException;
use src\Api\Exceptions\WrongRequestException;
use src\Api\Invoices\CreateInvoice\Cart;
use src\Api\Invoices\CreateInvoice\Request\CreateInvoiceRequest;
use src\Api\Invoices\CreateInvoice\Response\CreateInvoiceResponse;
use src\Api\Invoices\CreateInvoice\TaxMode;
use src\Api\Metadata;
use src\Api\Payments\CreatePayment\Request\CreatePaymentRequest;
use src\Api\Payments\CreatePayment\Request\CustomerPayerRequest;
use src\Api\Payments\CreatePayment\Request\PaymentFlowInstantRequest;
use src\Client\Client;
use src\Client\Sender;
use src\Exceptions\RequestException;

$recurrent = new RecurrentController();

foreach ($recurrent->getRecurrentPayments() as $payment) {
    $customer = $recurrent->getCustomer($payment['recurrent_customer_id']);
    $user = umiObjectsCollection::getInstance()->getObject($customer['user_id']);

    try {
        $invoice = $recurrent->createInvoice($payment, $user);
        $recurrent->createPayment($invoice, $customer['customer_id']);
        echo getLabel('RBK_MONEY_RECURRENT_SUCCESS') . $payment['id'] . PHP_EOL;
    } catch (Exception $exception) {
        echo $exception->getMessage();
    }
}

exit;

class RecurrentController
{
    /**
     * @var array
     */
    private $settings;

    /**
     * @var mysqlConnection
     */
    private $connection;

    /**
     * @var Sender
     */
    private $sender;

    public function __construct()
    {
        require_once 'src/settings.php';
        require_once 'src/autoload.php';

        $this->connection = ConnectionPool::getInstance()->getConnection();

        foreach ($this->connection->queryResult('SELECT * FROM `module_rbkmoney_settings`') as $setting) {
            $this->settings[$setting['code']] = $setting['value'];
        }

        $this->sender = new Sender(new Client(
            $this->settings['apiKey'],
            $this->settings['shopId'],
            RBK_MONEY_API_URL_SETTING
        ));
    }

    /**
     * @return mysqlQueryResult
     */
    public function getRecurrentPayments()
    {
        return $this->connection->queryResult('SELECT * FROM `module_rbkmoney_recurrent`');
    }

    /**
     * @param int $recurrentCustomerId
     *
     * @return array
     */
    public function getCustomer($recurrentCustomerId)
    {
        return $this->connection
            ->queryResult("SELECT * FROM `module_rbkmoney_recurrent_customers` WHERE `id` = $recurrentCustomerId")
            ->getIterator()->current();
    }

    /**
     * @param string $taxId
     *
     * @return string
     */
    private function getVatRate($taxId)
    {
        /**
         * @var umiObject $vatRate
         */
        $vatRate = umiObjectsCollection::getInstance()->getObject($taxId);

        foreach (TaxMode::$validValues as $validVatRate) {
            $vatPattern = addcslashes($validVatRate, '/');

            if (preg_match("/\s$vatPattern/", $vatRate->getName())) {
                return $validVatRate;
            }
        }

        return getLabel('RBK_MONEY_PARAMETER_NOT_USE');
    }

    /**
     * @param order $order
     *
     * @return string
     */
    private function getInvoiceId(order $order)
    {
        preg_match('/\d+/', $order->getObject()->getName(), $result);

        return current($result);
    }

    /**
     * @param array array     $payment
     * @param array umiObject $user
     *
     * @return CreateInvoiceResponse
     *
     * @throws Exception
     * @throws RequestException
     * @throws WrongDataException
     */
    public function createInvoice($payment, umiObject $user)
    {
        $this->includePaymentClasses();

        $orderItem = orderItem::create($payment['item_id']);
        $orderItem->setTaxRateId($payment['vat_rate']);
        $orderItem->setActualPrice($payment['amount']);
        // amount в UMI - это количество
        $orderItem->setAmount(1);

        $order = order::create();
        $order->appendItem($orderItem);
        $order->refresh();
        $order->commit();
        $order->order();

        $shopId = $this->settings['shopId'];
        $product = getLabel('RBK_MONEY_ORDER_PAYMENT') . " №{$this->getInvoiceId($order)} {$_SERVER['HTTP_HOST']}";

        $version = $this->connection
            ->queryResult("SELECT * FROM `cms_reg` WHERE `var` = 'system_version' ORDER BY `id` DESC limit 1")
            ->getIterator()
            ->current();

        $endDate = new DateTime();

        $createInvoice = new CreateInvoiceRequest(
            $shopId,
            $endDate->add(new DateInterval(INVOICE_LIFETIME_DATE_INTERVAL_SETTING)),
            $payment['currency'],
            $product,
            new Metadata([
                'orderId' => $order->getId(),
                'cms' => 'UMI.CMS',
                'cms_version' => $version['val'],
                'module' => MODULE_NAME_SETTING,
                'module_version' => MODULE_VERSION_SETTING,
            ])
        );

        if ('RBK_MONEY_PARAMETER_USE' === $this->settings['fiscalization']) {
            $cart = new Cart(
                "{$payment['name']} (1)",
                1,
                $this->prepareAmount($payment['amount'])
            );

            $vatRate = $this->getVatRate($payment['vat_rate']);

            if (getLabel('RBK_MONEY_PARAMETER_NOT_USE') !== $vatRate) {
                $cart->setTaxMode(new TaxMode($vatRate));
            }

            $createInvoice->addCart($cart);
        } else {
            $createInvoice->setAmount($this->prepareAmount($payment['amount']));
        }

        return $this->sender->sendCreateInvoiceRequest($createInvoice);
    }

    /**
     * @param float $price
     *
     * @return string
     */
    private function prepareAmount($price)
    {
        return number_format($price, 2, '', '');
    }

    /**
     * @param CreateInvoiceResponse $invoice
     * @param string                $customerId
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function createPayment(CreateInvoiceResponse $invoice, $customerId)
    {
        $payRequest = new CreatePaymentRequest(
            new PaymentFlowInstantRequest(),
            new CustomerPayerRequest($customerId),
            $invoice->id
        );

        $this->sender->sendCreatePaymentRequest($payRequest);
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
