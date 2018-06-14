<?php

use src\Api\ContactInfo;
use src\Api\Customers\CreateCustomer\Request\CreateCustomerRequest;
use src\Api\Exceptions\WrongDataException;
use src\Api\Exceptions\WrongRequestException;
use src\Api\Invoices\CreateInvoice\Response\CreateInvoiceResponse;
use src\Api\Invoices\CreateInvoice\TaxMode;
use src\Api\Metadata;
use src\Client\Sender;
use src\Exceptions\RequestException;

class Customers
{

    /**
     * @var Sender
     */
    private $sender;

    /**
     * @var array
     */
    private $settings;

    /**
     * @var mysqlConnection
     */
    private $connection;

    /**
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->connection = ConnectionPool::getInstance()->getConnection();

        foreach ($this->connection->queryResult('SELECT * FROM `module_rbkmoney_settings`') as $setting) {
            $this->settings[$setting['code']] = $setting['value'];
        }

        $cmsController = cmsController::getInstance();
        $language = $cmsController->getCurrentLang()->getPrefix();

        include "{$_SERVER['DOCUMENT_ROOT']}/classes/components/RBKmoney/i18n.$language.php";

        $this->langConst = $i18n;
        $this->sender = $sender;
    }

    /**
     * @return array
     */
    private function getRecurrentItems()
    {
        $items = $this->connection->queryResult('SELECT `article` FROM `module_rbkmoney_recurrent_items`');

        $result = '';

        foreach ($items as $item) {
            $result .= $item['article'] . PHP_EOL;
        }

        return explode(PHP_EOL, trim($result));
    }

    /**
     * @param CreateInvoiceResponse $invoiceResponse
     * @param umiObject             $user
     *
     * @return array
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    private function createCustomer(CreateInvoiceResponse $invoiceResponse, umiObject $user)
    {
        $contactInfo = new ContactInfo();

        if (!empty($email = $user->getValue('e-mail'))) {
            $contactInfo->setEmail($email);
        }

        $cmsVersion = $this->connection->queryResult("SELECT * FROM `cms_reg` WHERE `var` = 'system_version' ORDER BY `id` DESC limit 1");
        $version = $cmsVersion->getIterator()->current();

        $metadata = new Metadata([
            'shop' => $_SERVER['HTTP_HOST'],
            'userId' => $user->getId(),
            'firstInvoiceId' => $invoiceResponse->id,
            'cms' => 'UMI.CMS',
            'cms_version' => $version['val'],
            'module' => MODULE_NAME_SETTING,
            'module_version' => MODULE_VERSION_SETTING,
        ]);

        $createCustomer = $this->sender->sendCreateCustomerRequest(new CreateCustomerRequest(
            $this->settings['shopId'],
            $contactInfo,
            $metadata
        ));

        $this->connection->query("INSERT INTO `module_rbkmoney_recurrent_customers`
          (`user_id`, `customer_id`, `status`)
          VALUES ('{$user->getId()}', '{$createCustomer->customer->id}', '{$createCustomer->customer->status->getValue()}')");

        $customer = $this->connection->queryResult("SELECT * FROM `module_rbkmoney_recurrent_customers`
          WHERE  `customer_id` = '{$createCustomer->customer->id}'")->getIterator()->current();

        $customerParams = [
            'user_id' => $customer['user_id'],
            'customer_id' => $customer['customer_id'],
            'status' => $customer['status'],
            'hash' => $createCustomer->payload,
            'id' => $customer['id'],
        ];

        return $customerParams;
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
     * @param order                 $order
     * @param CreateInvoiceResponse $invoiceResponse
     * @param umiObject             $user
     *
     * @return null|string
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function createRecurrent(
        order $order,
        CreateInvoiceResponse $invoiceResponse,
        umiObject $user
    ) {
        $articles = [];
        $resultCustomer = null;

        $currency = strtoupper(mainConfiguration::getInstance()->get('system', 'default-currency'));
        if ($currency == 'RUR') {
            $currency = 'RUB';
        }

        foreach ($order->getItems() as $item) {
            $articles[] = $item->getItemElement()->getId();

            $items[$item->getItemElement()->getId()] = [
                'amount' => $item->getActualPrice(),
                'name' => $item->getName(),
                'item_id' => $item->getItemElement()->getId(),
                'currency' => $currency,
                'vat_rate' => $item->getTaxRateId(),
                'date' => new DateTime(),
                'status' => RECURRENT_UNREADY_STATUS,
                'order_id' => $order->getId(),
            ];
        }
        $intersections = array_intersect($articles, $this->getRecurrentItems());

        if (!empty($intersections)) {
            $customer = $this->connection->queryResult("SELECT * FROM `module_rbkmoney_recurrent_customers`
              WHERE `user_id` = '{$user->getId()}'");

            $customer = $customer->getIterator()->current();

            if (empty($customer)) {
                $customer = $this->createCustomer($invoiceResponse, $user);
            }

            foreach ($intersections as $article) {
                $this->saveRecurrent($customer['id'], $items[$article]);
            }
        }

        if (!empty($customer['hash'])) {
            $resultCustomer = 'data-customer-id="' . $customer['customer_id'] . '"
            data-customer-access-token="' . $customer['hash'] . '"';
        }

        return $resultCustomer;
    }

    /**
     * @param string $recurrentCustomerId
     * @param array  $item
     *
     * @return void
     */
    private function saveRecurrent($recurrentCustomerId, array $item)
    {
        $this->connection->query("INSERT INTO `module_rbkmoney_recurrent`
          (`recurrent_customer_id`, `amount`, `name`, `item_id`, `currency`, `vat_rate`, `date`, `status`, `order_id`)
            VALUES (
              '$recurrentCustomerId',
              '{$item['amount']}',
              '{$item['name']}',
              '{$item['item_id']}',
              '{$item['currency']}',
              '{$item['vat_rate']}',
              '{$item['date']->format('Y.m.d H:i:s')}',
              '{$item['status']}',
              '{$item['order_id']}'
            )"
        );
    }

    /**
     * @param order $order
     */
    public function setRecurrentReadyStatuses(order $order)
    {
        $articles = [];
        $recurrent = $this->connection->queryResult("SELECT * FROM `module_rbkmoney_recurrent` WHERE `order_id` = '{$order->getId()}'");

        if (!empty($recurrent)) {
            foreach ($order->getItems() as $item) {
                $articles[] = $item->getItemElement()->getId();
            }
            $intersections = array_intersect(
                $articles,
                $this->getRecurrentItems()
            );

            if (!empty($intersections)) {
                $this->connection->query("UPDATE `module_rbkmoney_recurrent` SET `status` = '" . RECURRENT_READY_STATUS . "' WHERE `order_id` = '{$order->getId()}'");
            }
        }
    }

}
