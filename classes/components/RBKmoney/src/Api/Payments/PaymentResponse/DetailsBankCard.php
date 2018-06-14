<?php

namespace src\Api\Payments\PaymentResponse;

/**
 * Детали платежного средства
 */
class DetailsBankCard extends PaymentToolDetails
{

    /**
     * Маскированый номер карты
     *
     * @var string
     */
    protected $cardNumberMask;

    /**
     * @var PaymentSystem
     */
    protected $paymentSystem;

    /**
     * @param string        $cardNumberMask
     * @param PaymentSystem $paymentSystem
     */
    public function __construct($cardNumberMask, PaymentSystem $paymentSystem)
    {
        $this->cardNumberMask = $cardNumberMask;
        $this->paymentSystem = $paymentSystem;
        $this->detailsType = parent::BANK_CARD;
    }

}
