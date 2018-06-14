<?php

namespace src\Api\Payments\PaymentResponse;

use DateTime;
use DateTimeZone;
use src\Api\Error;
use src\Api\Exceptions\WrongDataException;
use src\Api\Interfaces\ResponseInterface;
use src\Api\RBKmoneyDataObject;
use src\Api\Status;
use src\Helpers\ResponseHandler;
use stdClass;

/**
 * Родительский объект ответов с информацией о платеже
 */
class PaymentResponse extends RBKmoneyDataObject implements ResponseInterface
{

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
     * @var Status
     */
    public $status;

    /**
     * @var Error | null
     */
    public $error;

    /**
     * @param stdClass $responseObject
     *
     * @throws WrongDataException
     */
    public function __construct(stdClass $responseObject)
    {
        $timeZone = new DateTimeZone(date_default_timezone_get());
        $createdAt = new DateTime($responseObject->createdAt);

        $this->id = $responseObject->id;
        $this->invoiceId = $responseObject->invoiceID;
        $this->createdAt = $createdAt->setTimezone($timeZone);
        $this->amount = $responseObject->amount;
        $this->currency = $responseObject->currency;
        $this->flow = ResponseHandler::getFlow($responseObject->flow);
        $this->payer = ResponseHandler::getPayer($responseObject->payer);
        $this->status = new Status($responseObject->status);

        if (property_exists($responseObject, PROPERTY_ERROR)) {
            $this->error = ResponseHandler::getError($responseObject->{PROPERTY_ERROR});
        }
    }

}
