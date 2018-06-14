<?php

namespace src\Api\Payments\PaymentResponse;

use src\Api\RBKmoneyDataObject;

/**
 * Тип платежного средства
 */
abstract class PaymentToolDetails extends RBKmoneyDataObject
{

    /**
     * Типы информации о платежном средстве
     */
    const BANK_CARD = 'PaymentToolDetailsBankCard';
    const DIGITAL_WALLET = 'PaymentToolDetailsDigitalWallet';
    const PAYMENT_TERMINAL = 'PaymentToolDetailsPaymentTerminal';

    /**
     * @var string
     */
    public $detailsType;

}
