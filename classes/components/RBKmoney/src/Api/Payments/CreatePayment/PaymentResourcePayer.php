<?php

namespace src\Api\Payments\CreatePayment;

use src\Api\RBKmoneyDataObject;
use src\Api\ContactInfo;
use src\Api\Interfaces\PayerRequestInterface;

class PaymentResourcePayer extends RBKmoneyDataObject implements PayerRequestInterface
{

    /**
     * Тип платежного средства
     */
    const PAYER_TYPE = 'PaymentResourcePayer';

    /**
     * Тип платежного средства
     *
     * @var string
     */
    public $payerType = self::PAYER_TYPE;

    /**
     * Токен платежного средства, предоставленного плательщиком
     *
     * @var string
     */
    public $paymentToolToken;

    /**
     * Идентификатор платежной сессии
     *
     * @var string
     */
    public $paymentSession;

    /**
     * @var ContactInfo
     */
    public $contactInfo;

    /**
     * @param string      $paymentToolToken
     * @param string      $paymentSession
     * @param ContactInfo $info
     */
    public function __construct($paymentToolToken, $paymentSession, ContactInfo $info)
    {
        $this->paymentToolToken = $paymentToolToken;
        $this->paymentSession = $paymentSession;
        $this->contactInfo = $info;
    }

}
