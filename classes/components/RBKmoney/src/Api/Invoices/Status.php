<?php

namespace src\Api\Invoices;

use src\Api\Exceptions\WrongDataException;

/**
 * Статус инвойса
 */
class Status
{

    const UNPAID = 'unpaid';
    const CANCELLED = 'cancelled';
    const PAID = 'paid';
    const FULFILLED = 'fulfilled';

    /**
     * Валидные значения статуса инвойса
     */
    private $validValues = [
        self::UNPAID,
        self::CANCELLED,
        self::PAID,
        self::FULFILLED,
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
