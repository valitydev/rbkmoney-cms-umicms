<?php

namespace src\Api\Payments\PaymentResponse;

use src\Api\RBKmoneyDataObject;

abstract class Payer extends RBKmoneyDataObject
{

    const CUSTOMER_PAYER = 'CustomerPayer';
    const PAYMENT_RESOURCE_PAYER = 'PaymentResourcePayer';

    public $payerType;

}
