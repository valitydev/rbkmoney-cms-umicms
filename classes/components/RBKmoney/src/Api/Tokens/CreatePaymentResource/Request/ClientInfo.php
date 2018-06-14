<?php

namespace src\Api\Tokens\CreatePaymentResource\Request;

use src\Api\RBKmoneyDataObject;

class ClientInfo extends RBKmoneyDataObject
{

    /**
     * Уникальный отпечаток user agent'а плательщика
     *
     * @var string
     */
    public $fingerprint;

    /**
     * @param string $fingerprint
     */
    public function __construct($fingerprint)
    {
        $this->fingerprint = $fingerprint;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $properties = [];

        foreach ($this as $property => $value) {
            if (!empty($value)) {
                $properties[$property] = $value;
            }
        }

        return $properties;
    }

}
