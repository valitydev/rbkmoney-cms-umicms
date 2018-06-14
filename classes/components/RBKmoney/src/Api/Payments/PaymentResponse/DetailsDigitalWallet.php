<?php

namespace src\Api\Payments\PaymentResponse;

/**
 * Детали платежного средства
 */
class DetailsDigitalWallet extends PaymentToolDetails
{

    /**
     * @var string
     */
    protected $digitalWalletDetailsType;

    /**
     * @param string $digitalWalletDetailsType
     */
    public function __construct($digitalWalletDetailsType)
    {
        $this->digitalWalletDetailsType = $digitalWalletDetailsType;
        $this->detailsType = parent::DIGITAL_WALLET;
    }

}
