<?php

use src\Api\Exceptions\WrongDataException;
use src\Api\Exceptions\WrongRequestException;
use src\Api\Invoices\CreateInvoice\Cart;
use src\Api\Invoices\CreateInvoice\Request\CreateInvoiceRequest;
use src\Api\Invoices\CreateInvoice\Response\CreateInvoiceResponse;
use src\Api\Invoices\CreateInvoice\TaxMode;
use src\Api\Metadata;
use src\Api\Payments\CreatePayment\HoldType;
use src\Api\Webhooks\CreateWebhook\Request\CreateWebhookRequest;
use src\Api\Webhooks\CustomersTopicScope;
use src\Api\Webhooks\GetWebhooks\Request\GetWebhooksRequest;
use src\Api\Webhooks\InvoicesTopicScope;
use src\Api\Webhooks\WebhookResponse\WebhookResponse;
use src\Client\Client;
use src\Client\Sender;
use src\Exceptions\RBKmoneyException;
use src\Exceptions\RequestException;

class rbkmoneyPayment extends payment
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
     * @var string
     */
    private $moduleFolder;

    /**
     * @var Sender
     */
    private $sender;

    /**
     * @var array
     */
    private $langConst;

    /**
     * @param iUmiObject $object
     * @param            $order
     */
    public function __construct(iUmiObject $object, $order = null)
    {
        parent::__construct($object, $order);
        $this->connection = ConnectionPool::getInstance()->getConnection();

        foreach ($this->connection->queryResult('SELECT * FROM `module_rbkmoney_settings`') as $setting) {
            $this->settings[$setting['code']] = $setting['value'];
        }

        if (!empty($this->order)) {
            $currentSchema = ((isset($_SERVER['HTTPS']) && preg_match("/^on$/i", $_SERVER['HTTPS'])) ? 'https' : 'http');
            $this->settings['successUrl'] = "$currentSchema://{$_SERVER['HTTP_HOST']}/emarket/purchase/result/successful/";
        }

        $cmsController = cmsController::getInstance();
        $language = $cmsController->getCurrentLang()->getPrefix();

        $this->moduleFolder = $_SERVER['DOCUMENT_ROOT'] . '/classes/components/RBKmoney';

        include "$this->moduleFolder/src/autoload.php";
        include "$this->moduleFolder/src/settings.php";
        include "$this->moduleFolder/i18n.$language.php";

        $this->langConst = $i18n;

        $this->sender = new Sender(new Client(
            $this->settings['apiKey'],
            $this->settings['shopId'],
            RBK_MONEY_API_URL_SETTING
        ));
    }

    /**
     * @return int
     */
    public static function getOrderId()
    {
        return (int) getRequest('orderId');
    }

    /**
     * @return array
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    private function getNecessaryWebhooks()
    {
        $webhooks = $this->sender->sendGetWebhooksRequest(new GetWebhooksRequest());

        $statuses = [
            InvoicesTopicScope::INVOICES_TOPIC => [
                InvoicesTopicScope::INVOICE_PAID,
                InvoicesTopicScope::PAYMENT_PROCESSED,
                InvoicesTopicScope::PAYMENT_CAPTURED,
                InvoicesTopicScope::INVOICE_CANCELLED,
                InvoicesTopicScope::PAYMENT_REFUNDED,
                InvoicesTopicScope::PAYMENT_CANCELLED,
                InvoicesTopicScope::PAYMENT_PROCESSED,
            ],
            CustomersTopicScope::CUSTOMERS_TOPIC => [
                CustomersTopicScope::CUSTOMER_READY,
            ],
        ];

        /**
         * @var $webhook WebhookResponse
         */
        foreach ($webhooks->webhooks as $webhook) {
            if (empty($webhook) || RBK_MONEY_CALLBACK_URL !== $webhook->url) {
                continue;
            }
            if (InvoicesTopicScope::INVOICES_TOPIC === $webhook->scope->topic) {
                $statuses[InvoicesTopicScope::INVOICES_TOPIC] = array_diff(
                    $statuses[InvoicesTopicScope::INVOICES_TOPIC],
                    $webhook->scope->eventTypes
                );
            } else {
                $statuses[CustomersTopicScope::CUSTOMERS_TOPIC] = array_diff(
                    $statuses[CustomersTopicScope::CUSTOMERS_TOPIC],
                    $webhook->scope->eventTypes
                );
            }
        }

        if ($webhook->publicKey !== $this->settings['publicKey']) {
            $this->savePublicKey($webhook->publicKey);
        }

        return $statuses;
    }

    /**
     * @param string $key
     *
     * @return void
     */
    private function savePublicKey($key)
    {
        $this->connection->query("UPDATE `module_rbkmoney_settings` SET `value` = '$key' WHERE `code` = 'publicKey'");
    }

    /**
     * @param string $shopId
     * @param array  $types
     *
     * @return void
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    private function createPaymentWebhook($shopId, array $types)
    {
        $invoiceScope = new InvoicesTopicScope($shopId, $types);

        $webhook = $this->sender->sendCreateWebhookRequest(
            new CreateWebhookRequest($invoiceScope, RBK_MONEY_CALLBACK_URL)
        );

        $this->savePublicKey($webhook->publicKey);
    }

    /**
     * @param $orderId
     *
     * @return array | null
     */
    private function getInvoice($orderId)
    {
        $invoice = $this->connection->queryResult("SELECT * FROM `module_rbkmoney_invoices` WHERE `order_id` = $orderId");

        return $invoice->getIterator()->current();
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
     * @param $product
     *
     * @return CreateInvoiceResponse
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    private function createInvoice($product)
    {
        $fiscalization = ('RBK_MONEY_PARAMETER_USE' === $this->settings['fiscalization']);
        $carts = [];
        $sum = 0;

        $delivery = delivery::get($this->order->getDeliveryId());

        foreach ($this->order->getItems() as $item) {
            $quantity = $item->getAmount();
            $itemName = $item->getName();

            $discountPercent = $this->order->getDiscountPercent();

            if (0 !== $discountPercent) {
                $discount = $item->getActualPrice() / $discountPercent;
                $price = $item->getActualPrice() - $discount;
            } else {
                $price = $item->getActualPrice();
            }

            $sum += $price;

            $cart = new Cart(
                "$itemName ($quantity)",
                $quantity,
                $this->prepareAmount($price)
            );

            if ($fiscalization) {
                $vatRate = $this->getVatRate($item->getTaxRateId());

                if ($this->langConst['RBK_MONEY_PARAMETER_NOT_USE'] !== $vatRate) {
                    $cart->setTaxMode(new TaxMode($vatRate));
                }
            }

            $carts[] = $cart;
        }

        $version = $this->connection->queryResult("SELECT * FROM `cms_reg` WHERE `var` = 'system_version' ORDER BY `id` DESC limit 1");
        $cmsVersion = $version->getIterator()->current();
        $endDate = new DateTime();
        $currency = strtoupper(mainConfiguration::getInstance()->get('system', 'default-currency'));

        if ($currency == 'RUR') {
            $currency = 'RUB';
        }

        $createInvoice = new CreateInvoiceRequest(
            $this->settings['shopId'],
            $endDate->add(new DateInterval(INVOICE_LIFETIME_DATE_INTERVAL_SETTING)),
            $currency,
            $product,
            new Metadata([
                'orderId' => $this->order->getId(),
                'cms' => 'UMI.CMS',
                'cms_version' => $cmsVersion['val'],
                'module' => MODULE_NAME_SETTING,
                'module_version' => MODULE_VERSION_SETTING,
            ])
        );

        if (0 != $this->order->getDeliveryPrice()) {
            $deliveryCart = new Cart(
                $this->langConst['RBK_MONEY_DELIVERY'],
                1,
                $this->prepareAmount($this->order->getDeliveryPrice())
            );

            if ($fiscalization) {
                $deliveryVatRate = $this->getVatRate($delivery->getTaxRateId());

                if ($this->langConst['RBK_MONEY_PARAMETER_NOT_USE'] !== $deliveryVatRate) {
                    $deliveryCart->setTaxMode(new TaxMode($deliveryVatRate));
                }
            }
            $carts[] = $deliveryCart;
        }

        $createInvoice->addCarts($carts);

        $invoice = $this->sender->sendCreateInvoiceRequest($createInvoice);

        $this->saveInvoice($invoice);

        return $invoice;
    }

    /**
     * @param CreateInvoiceResponse $invoice
     *
     * @return void
     */
    private function saveInvoice(CreateInvoiceResponse $invoice)
    {
        $this->connection->query("INSERT INTO `module_rbkmoney_invoices`
          (`invoice_id`, `payload`, `end_date`, `order_id`) 
          VALUES ('$invoice->id', '$invoice->payload', '{$invoice->endDate->format('Y-m-d H:i:s')}', '{$this->order->getId()}')");
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

        return $this->langConst['RBK_MONEY_PARAMETER_NOT_USE'];
    }

    /**
     * @param string $shopId
     * @param array  $types
     *
     * @return void
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    private function createCustomerWebhook($shopId, array $types)
    {
        $scope = new CustomersTopicScope($shopId, $types);

        $webhook = $this->sender->sendCreateWebhookRequest(
            new CreateWebhookRequest($scope, RBK_MONEY_CALLBACK_URL)
        );

        $this->savePublicKey($webhook->publicKey);
    }

    /**
     * @return string
     */
    private function getInvoiceId()
    {
        preg_match('/\d+/', $this->order->getObject()->getName(), $result);

        return current($result);
    }

    /**
     * @param null $template
     *
     * @return string
     * @throws Exception
     */
    public function process($template = null)
    {
        $this->order->order();
        $shopId = $this->settings['shopId'];

        $orderId = $this->order->getId();
        $product = "{$this->langConst['RBK_MONEY_ORDER_PAYMENT']} №{$this->getInvoiceId()} {$_SERVER['HTTP_HOST']}";

        try {
            $necessaryWebhooks = $this->getNecessaryWebhooks();
            if (!empty($necessaryWebhooks[InvoicesTopicScope::INVOICES_TOPIC])) {
                $this->createPaymentWebhook(
                    $shopId,
                    $necessaryWebhooks[InvoicesTopicScope::INVOICES_TOPIC]
                );
            }
        } catch (RBKmoneyException $exception) {
            return $exception->getMessage();
        }

        // Даем пользователю 5 минут на заполнение даных карты
        $diff = new DateInterval(END_INVOICE_INTERVAL_SETTING);

        $rbkMoneyInvoice = $this->getInvoice($orderId);

        if (!empty($this->getInvoice($orderId))) {
            $endDate = new DateTime($rbkMoneyInvoice['end_date']);

            if ($endDate->sub($diff) > new DateTime()) {
                $payload = $rbkMoneyInvoice['payload'];
                $invoiceId = $rbkMoneyInvoice['invoice_id'];
            }
        }

        $user = umiObjectsCollection::getInstance()
            ->getObject($this->order->getValue('customer_id'));

        if (empty($payload)) {
            try {
                $invoiceResponse = $this->createInvoice($product);
            } catch (RBKmoneyException $exception) {
                return $exception->getMessage();
            }

            if (!empty($necessaryWebhooks[CustomersTopicScope::CUSTOMERS_TOPIC])) {
                try {
                    $this->createCustomerWebhook(
                        $shopId,
                        $necessaryWebhooks[CustomersTopicScope::CUSTOMERS_TOPIC]
                    );
                } catch (RBKmoneyException $exception) {
                    return $exception->getMessage();
                }
            }

            include "$this->moduleFolder/src/Customers.php";

            try {
                $customers = new Customers($this->sender);
                $customer = $customers->createRecurrent($this->order, $invoiceResponse, $user);
            } catch (RBKmoneyException $exception) {
                return $exception->getMessage();
            }

            $payload = $invoiceResponse->payload;
            $invoiceId = $invoiceResponse->id;
        }

        if (empty($customer)) {
            $out = 'data-invoice-id="' . $invoiceId . '"
            data-invoice-access-token="' . $payload . '"';
        } else {
            $out = $customer;
        }

        ob_end_clean();

        $holdExpiration = '';
        if ($holdType = ('RBK_MONEY_PAYMENT_TYPE_HOLD' === $this->settings['paymentType'])) {
            $holdExpiration = 'data-hold-expiration="' . $this->getHoldType()->getValue() . '"';
        }

        // При echo true заменяется на 1, а checkout воспринимает только true
        $requireCardHolder = ('RBK_MONEY_SHOW_PARAMETER' === $this->settings['cardHolder']) ? 'true' : 'false';
        $shadingCvv = ('RBK_MONEY_SHOW_PARAMETER' === $this->settings['shadingCvv']) ? 'true' : 'false';

        $templateParams = [
            'formAction' => "{$this->settings['successUrl']}",
            'orderId' => $this->order->getId(),
            'paymentSystem' => $this->langConst['module-RBKmoney'],
            'checkoutUrl' => RBK_MONEY_CHECKOUT_URL_SETTING,
            'holdType' => $holdType ? 'true' : 'false',
            'shadingCvv' => $shadingCvv,
            'requireCardHolder' => $requireCardHolder,
            'holdExpiration' => $holdExpiration,
            'product' => $product,
            'userEmail' => $user->getValue('e-mail'),
            'out' => $out,
            'label' => $this->langConst['RBK_MONEY_PAY'],
        ];

        list($templateString) = emarket::loadTemplates(
            "emarket/payment/rbkmoney/$template",
            'form_block'
        );

        return emarket::parseTemplate($templateString, $templateParams);
    }

    /**
     * @see RBKmoneyCallback
     */
    public function poll()
    {
        // Т.к. у RBKmoney используется подписка на вебхуки, каллбеки обрабатываются в RBKmoneyCallback
    }

}
