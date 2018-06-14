<?php

namespace src\Api\Payments\PaymentResponse;

use src\Api\RBKmoneyDataObject;

/**
 * Данные клиентского устройства плательщика
 */
class ClientInfo extends RBKmoneyDataObject
{

    /**
     * IP-адрес плательщика
     *
     * @var string | null
     */
    protected $ip;

    /**
     * Уникальный отпечаток user agent'а плательщика
     *
     * @var string
     */
    protected $fingerprint;

    /**
     * @param string $fingerprint
     */
    public function __construct($fingerprint)
    {
        $this->fingerprint = $fingerprint;
    }

    /**
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

}
