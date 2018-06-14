<?php

namespace src\Api\Payments\PaymentResponse;

/**
 * Детали платежного средства
 */
class DetailsPaymentTerminal extends PaymentToolDetails
{

    /**
     * @var string
     */
    protected $provider;

    /**
     * @param string $provider
     */
    public function __construct($provider)
    {
        $this->provider = $provider;
        $this->detailsType = parent::PAYMENT_TERMINAL;
    }

}
