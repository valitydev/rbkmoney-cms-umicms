<?php

namespace src\Api\Payments\PaymentResponse;

/**
 * Параметры созданного платежа
 */
class FlowInstant extends Flow
{

    public function __construct()
    {
        $this->type = self::INSTANT;
    }

}
