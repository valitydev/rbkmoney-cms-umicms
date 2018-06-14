<?php

namespace src\Api\Tokens\CreatePaymentResource\Request;

class DigitalWalletData extends PaymentTool
{

    const QIWI = 'DigitalWalletQIWI';

    /**
     * @var string
     */
    public $digitalWalletType;

    public function __construct()
    {
        $this->digitalWalletType = self::QIWI;
        $this->paymentToolType = self::DIGITAL_WALLET_DATA;
    }

}
