<?php

namespace src\Api\Payments\PaymentResponse;

use src\Api\Exceptions\WrongDataException;

/**
 * Платежная система
 */
class PaymentSystem
{

    const VISA = 'visa';
    const MASTERCARD = 'mastercard';
    const VISAELECTRON = 'visaelectron';
    const MAESTRO = 'maestro';
    const FORBRUGSFORENINGEN = 'forbrugsforeningen';
    const DANKORT = 'dankort';
    const AMEX = 'amex';
    const DINERSCLUB = 'dinersclub';
    const UNIONPAY = 'unionpay';
    const JCB = 'jcb';
    const NSPKMIR = 'nspkmir';
    const DISCOVER = 'discover';

    /**
     * Допустимые значения платежной системы
     */
    private $validValues = [
        self::VISA,
        self::MASTERCARD,
        self::VISAELECTRON,
        self::MAESTRO,
        self::FORBRUGSFORENINGEN,
        self::DANKORT,
        self::AMEX,
        self::DINERSCLUB,
        self::UNIONPAY,
        self::JCB,
        self::NSPKMIR,
        self::DISCOVER,
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
            throw new WrongDataException(getLabel('RBK_MONEY_WRONG_VALUE') . ' `paymentSystem`', RBK_MONEY_HTTP_CODE_BAD_REQUEST);
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
