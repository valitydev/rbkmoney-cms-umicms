<?php

namespace src\Api\Invoices\CreateInvoice\Response;

use src\Api\Exceptions\WrongDataException;
use src\Api\Invoices\InvoiceResponse\InvoiceResponse;
use stdClass;

/**
 * Объект ответа на запрос создания инвойса
 */
class CreateInvoiceResponse extends InvoiceResponse
{

    /**
     * Содержимое токена для доступа
     *
     * @var string
     */
    public $payload;

    /**
     * @param stdClass $responseObject
     *
     * @throws WrongDataException
     */
    public function __construct(stdClass $responseObject)
    {
        parent::__construct($responseObject->invoice);

        $this->payload = $responseObject->invoiceAccessToken->payload;
    }

}
