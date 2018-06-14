<?php

namespace src\Api\Tokens\CreatePaymentResource\Request;

use src\Api\Exceptions\WrongDataException;

class CardData extends PaymentTool
{

    /**
     * Номер банковской карты
     *
     * @var string
     */
    public $cardNumber;

    /**
     * Срок действия банковской карты
     *
     * @var string
     */
    public $expDate;

    /**
     * Код верификации
     *
     * @var string
     */
    public $cvv;

    /**
     * Имя держателя карты
     *
     * @var string | null
     */
    public $cardHolder;

    /**
     * @param string $cardNumber
     * @param string $expDate
     * @param string $cvv
     *
     * @throws WrongDataException
     */
    public function __construct($cardNumber, $expDate, $cvv)
    {
        if (!preg_match('/^\d{12,19}$/', $cardNumber)) {
            throw new WrongDataException(getLabel('RBK_MONEY_WRONG_VALUE') . ' `cardNumber`', RBK_MONEY_HTTP_CODE_BAD_REQUEST);
        }
        if (!preg_match('/^\d{2}\/(\d{2}|\d{4})$/', $expDate)) {
            throw new WrongDataException(getLabel('RBK_MONEY_WRONG_VALUE') . ' `expDate`', RBK_MONEY_HTTP_CODE_BAD_REQUEST);
        }
        if (!preg_match('/^\d{3,4}$/', $cvv)) {
            throw new WrongDataException(getLabel('RBK_MONEY_WRONG_VALUE') . ' `cvv`', RBK_MONEY_HTTP_CODE_BAD_REQUEST);
        }

        $this->cardNumber = $cardNumber;
        $this->expDate = $expDate;
        $this->cvv = $cvv;
        $this->paymentToolType = self::CARD_DATA;
    }

    /**
     * @param string $cardHolder
     *
     * @return CardData
     */
    public function setCardHolder($cardHolder)
    {
        $this->cardHolder = $cardHolder;

        return $this;
    }

}
