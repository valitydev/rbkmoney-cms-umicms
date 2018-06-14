<?php

namespace src\Api\Customers\CustomerResponse;

use src\Api\Exceptions\WrongDataException;

/**
 * Статус плательщика
 */
class Status
{

    const READY = 'ready';
    const UNREADY = 'unready';

    /**
     * Допустимые значения статуса плательщика
     */
    private $validValues = [
        self::READY,
        self::UNREADY,
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
