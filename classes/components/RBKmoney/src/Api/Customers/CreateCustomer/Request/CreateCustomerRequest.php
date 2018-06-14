<?php

namespace src\Api\Customers\CreateCustomer\Request;

use src\Api\ContactInfo;
use src\Api\Interfaces\PostRequestInterface;
use src\Api\Metadata;
use src\Api\RBKmoneyDataObject;

/**
 * Создать нового плательщика
 */
class CreateCustomerRequest extends RBKmoneyDataObject implements PostRequestInterface
{

    const PATH = '/processing/customers';

    /**
     * @var string
     */
    protected $shopID;

    /**
     * @var ContactInfo
     */
    protected $contactInfo;

    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @param string      $shopId
     * @param ContactInfo $contactInfo
     * @param Metadata    $metadata
     */
    public function __construct($shopId, ContactInfo $contactInfo, Metadata $metadata)
    {
        $this->shopID = $shopId;
        $this->contactInfo = $contactInfo;
        $this->metadata = $metadata;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $properties = [];

        foreach ($this as $property => $value) {
            if (!empty($value)) {
                $properties[$property] = (is_object($value)) ? $value->toArray() : $value;
            }
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
