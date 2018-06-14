<?php

namespace src\Api\Payments\PaymentResponse;

use DateTime;
use src\Api\Payments\CreatePayment\HoldType;

/**
 * Параметры созданного платежа
 */
class FlowHold extends Flow
{

    /**
     * Политика управления удержанием денежных средств
     *
     * @var HoldType
     */
    public $onHoldExpiration;

    /**
     * Дата и время, до которого происходит удержание денежных средств
     *
     * @var DateTime
     */
    protected $heldUntil;

    /**
     * @param HoldType $onHoldExpiration
     */
    public function __construct(HoldType $onHoldExpiration)
    {
        $this->onHoldExpiration = $onHoldExpiration;
        $this->type = self::HOLD;
    }

    /**
     * @param string $heldUntil
     *
     * @return FlowHold
     */
    public function setHeldUntil($heldUntil)
    {
        $this->heldUntil = new DateTime($heldUntil);

        return $this;
    }

}
