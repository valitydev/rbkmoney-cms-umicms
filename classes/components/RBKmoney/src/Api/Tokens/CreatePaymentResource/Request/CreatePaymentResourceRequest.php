<?php

namespace src\Api\Tokens\CreatePaymentResource\Request;

use src\Api\Interfaces\PostRequestInterface;
use src\Api\RBKmoneyDataObject;

class CreatePaymentResourceRequest extends RBKmoneyDataObject implements PostRequestInterface
{

    const PATH = '/processing/payment-resources';

    /**
     * @var PaymentTool
     */
    protected $paymentTool;

    /**
     * Данные клиентского устройства плательщика
     *
     * @var ClientInfo
     */
    protected $clientInfo;

    /**
     * @param PaymentTool $paymentTool
     * @param ClientInfo  $clientInfo
     */
    public function __construct(PaymentTool $paymentTool, ClientInfo $clientInfo)
    {
        $this->paymentTool = $paymentTool;
        $this->clientInfo = $paymentTool;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $properties = [];

        foreach ($this as $property => $value) {
            $properties[$property] = $value->toArray();
        }

        return $properties;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return self::PATH;
    }

}
