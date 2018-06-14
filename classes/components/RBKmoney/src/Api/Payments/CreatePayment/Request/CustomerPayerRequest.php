<?php

namespace src\Api\Payments\CreatePayment\Request;

use src\Api\Interfaces\PayerRequestInterface;
use src\Api\Payments\PaymentResponse\CustomerPayer;

class CustomerPayerRequest extends CustomerPayer implements PayerRequestInterface
{

}
