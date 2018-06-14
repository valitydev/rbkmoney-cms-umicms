<?php

namespace src\Api\Tokens\CreatePaymentResource\Request;

use src\Api\RBKmoneyDataObject;

abstract class PaymentTool extends RBKmoneyDataObject
{

    /**
     * Типы платежного средства
     */
    const CARD_DATA = 'CardData';
    const PAYMENT_TERMINAL_DATA = 'PaymentTerminalData';
    const DIGITAL_WALLET_DATA = 'DigitalWalletData';

    /**
     * @var string
     */
    public $paymentToolType;

    /**
     * @return array
     */
    public function toArray()
    {
        $properties = [];

        foreach ($this as $property => $value) {
            if (!empty($value)) {
                $properties[$property] = $value;
            }
        }

        return $properties;
    }

}
