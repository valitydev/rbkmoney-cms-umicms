<?php

include '../../../standalone.php';

$className = 'rbkmoney';
$paymentName = 'RBKmoney';

$objectTypesCollection = umiObjectTypesCollection::getInstance();
$objectsCollection = umiObjectsCollection::getInstance();
$parentTypeId = $objectTypesCollection->getTypeIdByGUID('emarket-payment');
$internalTypeId = $objectTypesCollection->getTypeIdByGUID('emarket-paymenttype');
$typeId = $objectTypesCollection->addType($parentTypeId, $paymentName);

$internalObjectId = $objectsCollection->addObject($paymentName, $internalTypeId);
$internalObject = $objectsCollection->getObject($internalObjectId);
$internalObject->setValue('class_name', $className);

$internalObject->setValue('payment_type_id', $typeId);
$internalObject->setValue('payment_type_guid', "user-emarket-payment-$typeId");
$internalObject->commit();

$type = $objectTypesCollection->getType($typeId);
$type->setGUID($internalObject->getValue('payment_type_guid'));
$type->commit();

$connection = ConnectionPool::getInstance()->getConnection();

$connection->queryResult('DROP TABLE IF EXISTS `module_rbkmoney_invoices`');
$connection->queryResult('CREATE TABLE `module_rbkmoney_invoices` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `invoice_id` VARCHAR(100) NOT NULL,
          `payload` TEXT NOT NULL,
          `end_date` DATETIME NOT NULL,
          `order_id` INT(11) NOT NULL,
          PRIMARY KEY (`id`))'
);

$connection->queryResult('DROP TABLE IF EXISTS `module_rbkmoney_recurrent`');
$connection->queryResult('CREATE TABLE `module_rbkmoney_recurrent` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `recurrent_customer_id` INT(10) UNSIGNED NOT NULL,
          `amount` FLOAT NOT NULL,
          `name` VARCHAR(250) NOT NULL,
          `item_id` VARCHAR(20) NOT NULL,
          `vat_rate` VARCHAR(20) NULL,
          `currency` VARCHAR(5) NOT NULL,
          `date` DATETIME NOT NULL,
          `status` VARCHAR(20) NOT NULL,
          `order_id` INT(11) NOT NULL,
          PRIMARY KEY (`id`))'
);

$connection->queryResult('DROP TABLE IF EXISTS `module_rbkmoney_recurrent_customers`');
$connection->queryResult('CREATE TABLE `module_rbkmoney_recurrent_customers` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `customer_id` VARCHAR(20) NOT NULL,
          `status` VARCHAR(20) NOT NULL,
          PRIMARY KEY (`id`))'
);

$connection->queryResult('DROP TABLE IF EXISTS `module_rbkmoney_recurrent_items`');
$connection->queryResult('CREATE TABLE `module_rbkmoney_recurrent_items` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `article` VARCHAR(250) NOT NULL,
          PRIMARY KEY (`id`))'
);

$connection->queryResult('DROP TABLE IF EXISTS `module_rbkmoney_settings`');
$connection->queryResult('CREATE TABLE `module_rbkmoney_settings` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `name` VARCHAR(100) NOT NULL,
          `code` VARCHAR(20) NOT NULL,
          `value` TEXT,
          PRIMARY KEY (`id`))'
);

$connection->queryResult("INSERT INTO `module_rbkmoney_settings`
		  (`name`, `code`)
		  VALUES
          ('RBK_MONEY_API_KEY', 'apiKey'),
          ('RBK_MONEY_SHOP_ID', 'shopId'),
          ('RBK_MONEY_PAYMENT_TYPE', 'paymentType'),
          ('RBK_MONEY_HOLD_EXPIRATION', 'holdExpiration'),
          ('RBK_MONEY_CARD_HOLDER', 'cardHolder'),
          ('RBK_MONEY_SHADING_CVV', 'shadingCvv'),
          ('RBK_MONEY_FISCALIZATION', 'fiscalization'),
          ('publicKey', 'publicKey'),
          ('RBK_MONEY_SAVE_LOGS', 'saveLogs'),
          ('RBK_MONEY_SUCCESS_ORDER_STATUS', 'successStatus'),
          ('RBK_MONEY_HOLD_ORDER_STATUS', 'holdStatus'),
          ('RBK_MONEY_CANCEL_ORDER_STATUS', 'cancelStatus'),
          ('RBK_MONEY_REFUND_ORDER_STATUS', 'refundStatus'),
          ('RBK_MONEY_DELIVERY_VAT_RATE', 'deliveryVatRate')"
);

/**
 * @var array $INFO реестр модуля
 */
$INFO = [
    'name' => $paymentName,
    'config' => '0',
    'default_method_admin' => 'settings',
];

/**
 * @var array $COMPONENTS файлы модуля
 */
$COMPONENTS = [
    './classes/components/RBKmoney/admin.php',
    './classes/components/RBKmoney/class.php',
    './classes/components/RBKmoney/i18n.ru.php',
    './classes/components/RBKmoney/i18n.en.php',
    './classes/components/RBKmoney/install.php',
    './classes/components/RBKmoney/lang.ru.php',
    './classes/components/RBKmoney/lang.en.php',
    './classes/components/RBKmoney/permissions.php',
    './classes/components/RBKmoney/RBKmoneyCallback.php',
    './classes/components/RBKmoney/recurrentCron.php',
    './classes/components/RBKmoney/logs/logs.txt',
    './classes/components/RBKmoney/src/Api/ContactInfo.php',
    './classes/components/RBKmoney/src/Api/Customers/CreateCustomer/Request/CreateCustomerRequest.php',
    './classes/components/RBKmoney/src/Api/Customers/CreateCustomer/Response/CreateCustomerResponse.php',
    './classes/components/RBKmoney/src/Api/Customers/CustomerResponse/CustomerResponse.php',
    './classes/components/RBKmoney/src/Api/Customers/CustomerResponse/Status.php',
    './classes/components/RBKmoney/src/Api/Error.php',
    './classes/components/RBKmoney/src/Api/Exceptions/WrongDataException.php',
    './classes/components/RBKmoney/src/Api/Exceptions/WrongRequestException.php',
    './classes/components/RBKmoney/src/Api/Interfaces/FlowRequestInterface.php',
    './classes/components/RBKmoney/src/Api/Interfaces/GetRequestInterface.php',
    './classes/components/RBKmoney/src/Api/Interfaces/PayerRequestInterface.php',
    './classes/components/RBKmoney/src/Api/Interfaces/PostRequestInterface.php',
    './classes/components/RBKmoney/src/Api/Interfaces/RequestInterface.php',
    './classes/components/RBKmoney/src/Api/Interfaces/ResponseInterface.php',
    './classes/components/RBKmoney/src/Api/Invoices/CreateInvoice/Cart.php',
    './classes/components/RBKmoney/src/Api/Invoices/CreateInvoice/Request/CreateInvoiceRequest.php',
    './classes/components/RBKmoney/src/Api/Invoices/CreateInvoice/Response/CreateInvoiceResponse.php',
    './classes/components/RBKmoney/src/Api/Invoices/CreateInvoice/TaxMode.php',
    './classes/components/RBKmoney/src/Api/Invoices/GetInvoiceById/Request/GetInvoiceByIdRequest.php',
    './classes/components/RBKmoney/src/Api/Invoices/GetInvoiceById/Response/GetInvoiceByIdResponse.php',
    './classes/components/RBKmoney/src/Api/Invoices/InvoiceResponse/CartResponse.php',
    './classes/components/RBKmoney/src/Api/Invoices/InvoiceResponse/InvoiceResponse.php',
    './classes/components/RBKmoney/src/Api/Invoices/Status.php',
    './classes/components/RBKmoney/src/Api/Metadata.php',
    './classes/components/RBKmoney/src/Api/Payments/CancelPayment/Request/CancelPaymentRequest.php',
    './classes/components/RBKmoney/src/Api/Payments/CapturePayment/Request/CapturePaymentRequest.php',
    './classes/components/RBKmoney/src/Api/Payments/CreatePayment/HoldType.php',
    './classes/components/RBKmoney/src/Api/Payments/CreatePayment/PayerType.php',
    './classes/components/RBKmoney/src/Api/Payments/CreatePayment/PaymentResourcePayer.php',
    './classes/components/RBKmoney/src/Api/Payments/CreatePayment/Request/CreatePaymentRequest.php',
    './classes/components/RBKmoney/src/Api/Payments/CreatePayment/Request/CustomerPayerRequest.php',
    './classes/components/RBKmoney/src/Api/Payments/CreatePayment/Request/PaymentFlowHoldRequest.php',
    './classes/components/RBKmoney/src/Api/Payments/CreatePayment/Request/PaymentFlowInstantRequest.php',
    './classes/components/RBKmoney/src/Api/Payments/CreatePayment/Response/CreatePaymentResponse.php',
    './classes/components/RBKmoney/src/Api/Payments/CreateRefund/Request/CreateRefundRequest.php',
    './classes/components/RBKmoney/src/Api/Payments/PaymentResponse/ClientInfo.php',
    './classes/components/RBKmoney/src/Api/Payments/PaymentResponse/CustomerPayer.php',
    './classes/components/RBKmoney/src/Api/Payments/PaymentResponse/DetailsBankCard.php',
    './classes/components/RBKmoney/src/Api/Payments/PaymentResponse/DetailsDigitalWallet.php',
    './classes/components/RBKmoney/src/Api/Payments/PaymentResponse/DetailsPaymentTerminal.php',
    './classes/components/RBKmoney/src/Api/Payments/PaymentResponse/Flow.php',
    './classes/components/RBKmoney/src/Api/Payments/PaymentResponse/FlowHold.php',
    './classes/components/RBKmoney/src/Api/Payments/PaymentResponse/FlowInstant.php',
    './classes/components/RBKmoney/src/Api/Payments/PaymentResponse/Payer.php',
    './classes/components/RBKmoney/src/Api/Payments/PaymentResponse/PaymentResourcePayer.php',
    './classes/components/RBKmoney/src/Api/Payments/PaymentResponse/PaymentResponse.php',
    './classes/components/RBKmoney/src/Api/Payments/PaymentResponse/PaymentSystem.php',
    './classes/components/RBKmoney/src/Api/Payments/PaymentResponse/PaymentToolDetails.php',
    './classes/components/RBKmoney/src/Api/Payments/RefundResponse/RefundResponse.php',
    './classes/components/RBKmoney/src/Api/Payments/RefundResponse/Status.php',
    './classes/components/RBKmoney/src/Api/RBKmoneyDataObject.php',
    './classes/components/RBKmoney/src/Api/Search/PaymentMethod.php',
    './classes/components/RBKmoney/src/Api/Search/SearchPayments/Request/SearchPaymentsRequest.php',
    './classes/components/RBKmoney/src/Api/Search/SearchPayments/Response/GeoLocation.php',
    './classes/components/RBKmoney/src/Api/Search/SearchPayments/Response/Payment.php',
    './classes/components/RBKmoney/src/Api/Search/SearchPayments/Response/SearchPaymentsResponse.php',
    './classes/components/RBKmoney/src/Api/Status.php',
    './classes/components/RBKmoney/src/Api/Tokens/CreatePaymentResource/Request/CardData.php',
    './classes/components/RBKmoney/src/Api/Tokens/CreatePaymentResource/Request/ClientInfo.php',
    './classes/components/RBKmoney/src/Api/Tokens/CreatePaymentResource/Request/CreatePaymentResourceRequest.php',
    './classes/components/RBKmoney/src/Api/Tokens/CreatePaymentResource/Request/DigitalWalletData.php',
    './classes/components/RBKmoney/src/Api/Tokens/CreatePaymentResource/Request/PaymentTerminalData.php',
    './classes/components/RBKmoney/src/Api/Tokens/CreatePaymentResource/Request/PaymentTool.php',
    './classes/components/RBKmoney/src/Api/Tokens/CreatePaymentResource/Request/TerminalProvider.php',
    './classes/components/RBKmoney/src/Api/Tokens/CreatePaymentResource/Response/CreatePaymentResourceResponse.php',
    './classes/components/RBKmoney/src/Api/Webhooks/CreateWebhook/Request/CreateWebhookRequest.php',
    './classes/components/RBKmoney/src/Api/Webhooks/CreateWebhook/Response/CreateWebhookResponse.php',
    './classes/components/RBKmoney/src/Api/Webhooks/CustomersTopicScope.php',
    './classes/components/RBKmoney/src/Api/Webhooks/GetWebhooks/Request/GetWebhooksRequest.php',
    './classes/components/RBKmoney/src/Api/Webhooks/GetWebhooks/Response/GetWebhooksResponse.php',
    './classes/components/RBKmoney/src/Api/Webhooks/InvoicesTopicScope.php',
    './classes/components/RBKmoney/src/Api/Webhooks/WebhookResponse/WebhookResponse.php',
    './classes/components/RBKmoney/src/Api/Webhooks/WebhookScope.php',
    './classes/components/RBKmoney/src/Client/Client.php',
    './classes/components/RBKmoney/src/Client/Sender.php',
    './classes/components/RBKmoney/src/Customers.php',
    './classes/components/RBKmoney/src/Exceptions/RBKmoneyException.php',
    './classes/components/RBKmoney/src/Exceptions/RequestException.php',
    './classes/components/RBKmoney/src/Helpers/Log.php',
    './classes/components/RBKmoney/src/Helpers/Logger.php',
    './classes/components/RBKmoney/src/Helpers/Paginator.php',
    './classes/components/RBKmoney/src/Helpers/ResponseHandler.php',
    './classes/components/RBKmoney/src/Interfaces/ClientInterface.php',
    './classes/components/RBKmoney/src/autoload.php',
    './classes/components/RBKmoney/src/settings.php',
];