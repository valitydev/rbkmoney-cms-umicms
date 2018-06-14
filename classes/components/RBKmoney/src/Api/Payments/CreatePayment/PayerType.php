<?php

namespace src\Api\Payments\CreatePayment;

use src\Api\Exceptions\WrongDataException;

/**
 * Тип платежного средства
 */
class PayerType
{

    const CUSTOMER_PAYER = 'CustomerPayer';
    const PAYMENT_RESOURCE_PAYER = 'PaymentResourcePayer';

    /**
     * Массив возможных типов
     */
    private $validValues = [
        self::CUSTOMER_PAYER,
        self::PAYMENT_RESOURCE_PAYER,
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
            throw new WrongDataException(getLabel('RBK_MONEY_WRONG_VALUE') . ' `payerType`', RBK_MONEY_HTTP_CODE_BAD_REQUEST);
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
