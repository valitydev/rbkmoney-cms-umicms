<?php

namespace src\Api\Search\SearchPayments\Response;

use DateTime;
use DateTimeZone;
use src\Api\Error;
use src\Api\Exceptions\WrongDataException;
use src\Api\Interfaces\ResponseInterface;
use src\Api\Metadata;
use src\Api\Payments\PaymentResponse\Flow;
use src\Api\Payments\PaymentResponse\Payer;
use src\Api\RBKmoneyDataObject;
use src\Api\Status;
use src\Helpers\ResponseHandler;
use stdClass;

class Payment extends RBKmoneyDataObject implements ResponseInterface
{

    /**
     * @var Status
     */
    public $status;

    /**
     * @var Error | null
     */
    public $error;

    /**
     * Идентификатор платежа
     *
     * @var string
     */
    public $id;

    /**
     * Идентификатор инвойса, в рамках которого был создан платеж
     *
     * @var string
     */
    public $invoiceId;

    /**
     * Идентификатор магазина, в рамках которого был создан платеж
     *
     * @var string | null
     */
    public $shopId;

    /**
     * Дата и время создания
     *
     * @var DateTime
     */
    public $createdAt;

    /**
     * Стоимость предлагаемых товаров или услуг, в минорных денежных единицах,
     * например в копейках в случае указания российских рублей в качестве валюты.
     *
     * @var int
     */
    public $amount;

    /**
     * Комиссия системы, в минорных денежных единицах
     *
     * @var int | null
     */
    public $fee;

    /**
     * Валюта, символьный код согласно ISO 4217.
     *
     * @var string
     */
    public $currency;

    /**
     * @var Flow
     */
    public $flow;

    /**
     * @var Payer
     */
    public $payer;

    /**
     * @var GeoLocation
     */
    public $geoLocationInfo;

    /**
     * Связанные с платежом метаданные
     *
     * @var Metadata
     */
    public $metadata;

    /**
     * @param stdClass $responseObject
     *
     * @throws WrongDataException
     */
    public function __construct(stdClass $responseObject)
    {
        $timeZone = new DateTimeZone(date_default_timezone_get());
        $createdAt = new DateTime($responseObject->createdAt);

        $this->status = new Status($responseObject->status);
        $this->id = $responseObject->id;
        $this->invoiceId = $responseObject->invoiceID;
        $this->createdAt = $createdAt->setTimezone($timeZone);
        $this->amount = $responseObject->amount;
        $this->currency = $responseObject->currency;
        $this->flow = ResponseHandler::getFlow($responseObject->flow);
        $this->payer = ResponseHandler::getPayer($responseObject->payer);

        if (property_exists($responseObject, PROPERTY_ERROR)) {
            $this->error = ResponseHandler::getError($responseObject->{PROPERTY_ERROR});
        }

        if (property_exists($responseObject, PROPERTY_SHOP_ID)) {
            $this->shopId = $responseObject->{PROPERTY_SHOP_ID};
        }

        if (property_exists($responseObject, PROPERTY_FEE)) {
            $this->fee = $responseObject->{PROPERTY_FEE};
        }

        if (property_exists($responseObject, PROPERTY_GEO_LOCATION_INFO)) {
            $location = $responseObject->{PROPERTY_GEO_LOCATION_INFO};
            $this->geoLocationInfo = new GeoLocation($location->cityGeoID, $location->countryGeoID);
        }

        if (property_exists($responseObject, PROPERTY_METADATA)) {
            if (false !== current($responseObject->{PROPERTY_METADATA})) {
                $this->metadata = new Metadata((array)current($responseObject->{PROPERTY_METADATA}));
            }
        }
    }

}
