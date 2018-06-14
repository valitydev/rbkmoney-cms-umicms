<?php

namespace src\Api\Payments\CreatePayment\Request;

use src\Api\Interfaces\FlowRequestInterface;
use src\Api\Payments\PaymentResponse\FlowInstant;

/**
 * Параметры создаваемого платежа
 */
class PaymentFlowInstantRequest extends FlowInstant implements FlowRequestInterface
{

}
