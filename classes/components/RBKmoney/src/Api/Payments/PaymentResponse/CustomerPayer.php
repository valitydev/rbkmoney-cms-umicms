<?php

namespace src\Api\Payments\PaymentResponse;

class CustomerPayer extends Payer
{

    /**
     * Идентификатор плательщика
     *
     * @var string
     */
    public $customerID;

    /**
     * @param string $customerId
     */
    public function __construct($customerId)
    {
        $this->customerID = $customerId;
        $this->payerType = self::CUSTOMER_PAYER;
    }

}
