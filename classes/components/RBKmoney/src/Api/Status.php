<?php

namespace src\Api;

use src\Api\Exceptions\WrongDataException;

/**
 * Статус платежа
 */
class Status
{

    const STARTED = 'started';
    const PENDING = 'pending';
    const PROCESSED = 'processed';
    const CAPTURED = 'captured';
    const CHARGED_BACK = 'charged-back';
    const CANCELLED = 'cancelled';
    const REFUNDED = 'refunded';
    const FAILED = 'failed';

    /**
     * Допустимые значения статуса платежа
     */
    private $validValues = [
        self::STARTED,
        self::PENDING,
        self::PROCESSED,
        self::CAPTURED,
        self::CHARGED_BACK,
        self::CANCELLED,
        self::REFUNDED,
        self::FAILED,
    ];

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     *
     * @throws WrongDataException
     */
    public function __construct($value)
    {
        if (!in_array($value, $this->validValues)) {
            throw new WrongDataException(getLabel('RBK_MONEY_WRONG_VALUE') . ' `status`', RBK_MONEY_HTTP_CODE_BAD_REQUEST);
        }

        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

}
