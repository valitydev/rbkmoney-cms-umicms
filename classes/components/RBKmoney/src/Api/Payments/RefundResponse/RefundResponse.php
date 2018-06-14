<?php

namespace src\Api\Payments\RefundResponse;

use DateTime;
use DateTimeZone;
use src\Api\Error;
use src\Api\Exceptions\WrongDataException;
use src\Api\RBKmoneyDataObject;
use src\Helpers\ResponseHandler;
use stdClass;

/**
 * Объект ответа на запрос возврата указанного платежа
 */
class RefundResponse extends RBKmoneyDataObject
{

    /**
     * Идентификатор возврата
     *
     * @var string
     */
    protected $id;

    /**
     * Дата и время осуществления
     *
     * @var DateTime
     */
    protected $createdAt;

    /**
     * Причина осуществления возврата
     *
     * @var string | null
     */
    protected $reason;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var Error | null
     */
    protected $error;

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
        $this->createdAt = $createdAt->setTimezone($timeZone);
        $this->status = new Status($responseObject->status);

        if (property_exists($responseObject, PROPERTY_REASON)) {
            $this->reason = $responseObject->{PROPERTY_REASON};
        }

        if (property_exists($responseObject, PROPERTY_ERROR)) {
            $this->error = ResponseHandler::getError($responseObject->{PROPERTY_ERROR});
        }
    }

}
