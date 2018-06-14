<?php

namespace src\Api\Invoices\GetInvoiceById\Request;

use src\Api\Interfaces\GetRequestInterface;
use src\Api\RBKmoneyDataObject;

/**
 * Получить историю указанного инвойса в виде набора событий
 */
class GetInvoiceByIdRequest extends RBKmoneyDataObject implements GetRequestInterface
{

    const PATH = '/processing/invoices/{invoiceID}';

    /**
     * @var string
     */
    protected $invoiceId;

    /**
     * @param string $invoiceId
     */
    public function __construct($invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return str_replace('{invoiceID}', $this->invoiceId, self::PATH);
    }

}
