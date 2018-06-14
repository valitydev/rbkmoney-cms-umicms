<?php

namespace src\Api\Webhooks;

use src\Api\Exceptions\WrongDataException;

class CustomersTopicScope extends WebhookScope
{

    /**
     * Предмет оповещений
     */
    const CUSTOMERS_TOPIC = 'CustomersTopic';

    /**
     * Плательщик создан
     */
    const CUSTOMER_CREATED = 'CustomerCreated';

    /**
     * Плательщик удален
     */
    const CUSTOMER_DELETED = 'CustomerDeleted';

    /**
     * Плательщик готов
     */
    const CUSTOMER_READY = 'CustomerReady';

    /**
     * Привязка к плательщику запущена
     */
    const CUSTOMER_BINDING_STARTED = 'CustomerBindingStarted';

    /**
     * Привязка к плательщику успешно завершена
     */
    const CUSTOMER_BINDING_SUCCEEDED = 'CustomerBindingSucceeded';

    /**
     * Привязка к плательщику завершена неудачей
     */
    const CUSTOMER_BINDING_FAILED = 'CustomerBindingFailed';

    /**
     * Допустимые значения типов событий
     */
    private $validTypes = [
        self::CUSTOMER_CREATED,
        self::CUSTOMER_DELETED,
        self::CUSTOMER_READY,
        self::CUSTOMER_BINDING_STARTED,
        self::CUSTOMER_BINDING_SUCCEEDED,
        self::CUSTOMER_BINDING_FAILED,
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
        $this->topic = self::CUSTOMERS_TOPIC;

        $diff = array_diff($eventTypes, $this->validTypes);

        if (!empty($diff)) {
            throw new WrongDataException(getLabel('RBK_MONEY_WRONG_VALUE') . ' `eventTypes`', RBK_MONEY_HTTP_CODE_BAD_REQUEST);
        }

        $this->eventTypes = $eventTypes;
    }

}
