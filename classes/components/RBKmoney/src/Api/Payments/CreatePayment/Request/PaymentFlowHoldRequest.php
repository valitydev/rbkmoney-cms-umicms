<?php

namespace src\Api\Payments\CreatePayment\Request;

use src\Api\Interfaces\FlowRequestInterface;
use src\Api\Payments\PaymentResponse\FlowHold;

/**
 * Параметры создаваемого платежа
 */
class PaymentFlowHoldRequest extends FlowHold implements FlowRequestInterface
{

}
