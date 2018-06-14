<?php

namespace src\Api\Payments\PaymentResponse;

use src\Api\ContactInfo;

class PaymentResourcePayer extends Payer
{

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
     * @var PaymentToolDetails | null
     */
    public $paymentToolDetails;

    /**
     * @var ClientInfo | null
     */
    public $clientInfo;

    /**
     * @var ContactInfo
     */
    public $contactInfo;

    /**
     * @param string      $paymentToolToken
     * @param string      $paymentSession
     * @param ContactInfo $contactInfo
     */
    public function __construct($paymentToolToken, $paymentSession, ContactInfo $contactInfo)
    {
        $this->paymentToolToken = $paymentToolToken;
        $this->paymentSession = $paymentSession;
        $this->contactInfo = $contactInfo;
        $this->payerType = self::PAYMENT_RESOURCE_PAYER;
    }

    /**
     * @param PaymentToolDetails $paymentToolDetails
     *
     * @return PaymentResourcePayer
     */
    public function setPaymentToolDetails(PaymentToolDetails $paymentToolDetails)
    {
        $this->paymentToolDetails = $paymentToolDetails;

        return $this;
    }

    /**
     * @param ClientInfo $clientInfo
     *
     * @return PaymentResourcePayer
     */
    public function setClientInfo(ClientInfo $clientInfo)
    {
        $this->clientInfo = $clientInfo;

        return $this;
    }

}
