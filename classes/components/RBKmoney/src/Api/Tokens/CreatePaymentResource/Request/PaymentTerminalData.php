<?php

namespace src\Api\Tokens\CreatePaymentResource\Request;

class PaymentTerminalData extends PaymentTool
{

    /**
     * Провайдер терминальной сети
     *
     * @var string
     */
    public $provider;

    /**
     * @param TerminalProvider $provider
     */
    public function __construct(TerminalProvider $provider)
    {
        $this->provider = $provider;
        $this->paymentToolType = self::PAYMENT_TERMINAL_DATA;
    }

}
