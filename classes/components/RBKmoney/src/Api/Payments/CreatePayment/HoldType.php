<?php

namespace src\Api\Payments\CreatePayment;

use src\Api\Exceptions\WrongDataException;

/**
 * Политика управления удержанием денежных средств
 */
class HoldType
{

    const CANCEL = 'cancel';
    const CAPTURE = 'capture';

    /**
     * Массив возможных типов
     */
    private $validValues = [
        self::CANCEL,
        self::CAPTURE,
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
            throw new WrongDataException(getLabel('RBK_MONEY_WRONG_VALUE') . ' `onHoldExpiration`', RBK_MONEY_HTTP_CODE_BAD_REQUEST);
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
