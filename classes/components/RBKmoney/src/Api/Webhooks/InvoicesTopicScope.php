<?php

namespace src\Api\Webhooks;

use src\Api\Exceptions\WrongDataException;

class InvoicesTopicScope extends WebhookScope
{

    /**
     * Предмет оповещений
     */
    const INVOICES_TOPIC = 'InvoicesTopic';

    /**
     * Создан новый инвойс
     */
    const INVOICE_CREATED = 'InvoiceCreated';

    /**
     * Инвойс перешел в состояние "Оплачен"
     */
    const INVOICE_PAID = 'InvoicePaid';

    /**
     * Инвойс отменен по истечению срока давности
     */
    const INVOICE_CANCELLED = 'InvoiceCancelled';

    /**
     * Инвойс успешно погашен
     */
    const INVOICE_FULFILLED = 'InvoiceFulfilled';

    /**
     * Создан платеж
     */
    const PAYMENT_STARTED = 'PaymentStarted';

    /**
     * Платеж в обработке
     */
    const PAYMENT_PROCESSED = 'PaymentProcessed';

    /**
     * Платеж успешно завершен
     */
    const PAYMENT_CAPTURED = 'PaymentCaptured';

    /**
     * Платеж успешно отменен
     */
    const PAYMENT_CANCELLED = 'PaymentCancelled';

    /**
     * Платеж успешно возвращен
     */
    const PAYMENT_REFUNDED = 'PaymentRefunded';

    /**
     * При проведении платежа возникла ошибка
     */
    const PAYMENT_FAILED = 'PaymentFailed';

    /**
     * Допустимые значения типов событий
     */
    private $validTypes = [
        self::INVOICE_CREATED,
        self::INVOICE_PAID,
        self::INVOICE_CANCELLED,
        self::INVOICE_FULFILLED,
        self::PAYMENT_STARTED,
        self::PAYMENT_PROCESSED,
        self::PAYMENT_CAPTURED,
        self::PAYMENT_CANCELLED,
        self::PAYMENT_REFUNDED,
        self::PAYMENT_FAILED,
    ];

    /**
     * @param string $shopID
     * @param array  $eventTypes
     *
     * @throws WrongDataException
     */
    public function __construct($shopID, array $eventTypes)
    {
        $this->shopID = $shopID;
        $this->topic = self::INVOICES_TOPIC;

        $diff = array_diff($eventTypes, $this->validTypes);

        if (!empty($diff)) {
            throw new WrongDataException(getLabel('RBK_MONEY_WRONG_VALUE') . ' `eventTypes`', RBK_MONEY_HTTP_CODE_BAD_REQUEST);
        }

        $this->eventTypes = $eventTypes;
    }

}
