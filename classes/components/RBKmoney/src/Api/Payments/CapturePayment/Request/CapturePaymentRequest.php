<?php

namespace src\Api\Payments\CapturePayment\Request;

use src\Api\Interfaces\PostRequestInterface;
use src\Api\RBKmoneyDataObject;

/**
 * Подтвердить указанный платеж.
 */
class CapturePaymentRequest extends RBKmoneyDataObject implements PostRequestInterface
{

    const PATH = '/processing/invoices/{invoiceID}/payments/{paymentID}/capture';

    /**
     * @var string
     */
    protected $invoiceId;

    /**
     * @var string
     */
    protected $paymentId;

    /**
     * Причина совершения операции
     *
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
        $this->paymentId = $paymentId;
        $this->reason = $reason;
        $this->invoiceId = $invoiceId;
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
