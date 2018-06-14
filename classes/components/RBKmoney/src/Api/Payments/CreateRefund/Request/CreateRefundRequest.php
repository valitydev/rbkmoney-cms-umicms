<?php

namespace src\Api\Payments\CreateRefund\Request;

use src\Api\Interfaces\PostRequestInterface;
use src\Api\RBKmoneyDataObject;

/**
 * Запрос на возврат указанного платежа
 */
class CreateRefundRequest extends RBKmoneyDataObject implements PostRequestInterface
{

    const PATH = '/processing/invoices/{invoiceID}/payments/{paymentID}/refunds';

    /**
     * @var string
     */
    protected $invoiceId;

    /**
     * @var string
     */
    protected $paymentId;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @param string $invoiceId
     * @param string $paymentId
     * @param string $reason
     */
    public function __construct($invoiceId, $paymentId, $reason)
    {
        $this->invoiceId = $invoiceId;
        $this->paymentId = $paymentId;
        $this->reason = $reason;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'reason' => $this->reason,
        ];
    }

    /**
     * @return string
     */
    public function getPath()
    {
        $search = [
            '{invoiceID}',
            '{paymentID}',
        ];

        $replace = [
            $this->invoiceId,
            $this->paymentId,
        ];

        return str_replace($search, $replace, self::PATH);
    }

}
