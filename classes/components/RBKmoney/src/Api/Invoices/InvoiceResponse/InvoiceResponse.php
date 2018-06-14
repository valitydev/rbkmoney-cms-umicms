<?php

namespace src\Api\Invoices\InvoiceResponse;

use DateTime;
use DateTimeZone;
use src\Api\Exceptions\WrongDataException;
use src\Api\Interfaces\ResponseInterface;
use src\Api\Invoices\CreateInvoice\Cart;
use src\Api\Metadata;
use src\Api\Invoices\Status;
use src\Api\RBKmoneyDataObject;
use src\Helpers\ResponseHandler;
use stdClass;

/**
 * Родительский объект ответов с информацией об инвойсе
 */
class InvoiceResponse extends RBKmoneyDataObject implements ResponseInterface
{

    /**
     * Идентификатор инвойса
     *
     * @var string
     */
    public $id;

    /**
     * Идентификатор магазина
     *
     * @var string
     */
    public $shopId;

    /**
     * Дата и время создания инвойса
     *
     * @var DateTime
     */
    public $createdAt;

    /**
     * Дата и время окончания действия инвойса
     *
     * @var DateTime
     */
    public $endDate;

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
     * Наименование предлагаемых товаров или услуг
     *
     * @var string
     */
    public $product;

    /**
     * Описание предлагаемых товаров или услуг
     *
     * @var string | null
     */
    public $description;

    /**
     * Идентификатор шаблона (для инвойсов, созданных по шаблону)
     *
     * @var string
     */
    public $invoiceTemplateId;

    /**
     * Корзина с набором позиций продаваемых товаров или услуг
     *
     * @var array | Cart[] | null
     */
    public $cart;

    /**
     * Связанные с инвойсом метаданные
     *
     * @var Metadata
     */
    public $metadata;

    /**
     * Статус инвойса
     *
     * @var Status
     */
    public $status;

    /**
     * Причина отмены или погашения инвойса
     *
     * @var string
     */
    public $reason;

    /**
     * @param stdClass $invoice
     *
     * @throws WrongDataException
     */
    public function __construct(stdClass $invoice)
    {
        $timeZone = new DateTimeZone(date_default_timezone_get());
        $createdAt = new DateTime($invoice->createdAt);
        $endDate = new DateTime($invoice->dueDate);

        $this->id = $invoice->id;
        $this->shopId = $invoice->shopID;
        $this->createdAt = $createdAt->setTimezone($timeZone);
        $this->endDate = $endDate->setTimezone($timeZone);
        $this->amount = $invoice->amount;
        $this->currency = $invoice->currency;
        $this->product = $invoice->product;
        $this->metadata = new Metadata((array)$invoice->metadata);
        $this->status = new Status($invoice->status);

        if (property_exists($invoice, PROPERTY_DESCRIPTION)) {
            $this->description = $invoice->{PROPERTY_DESCRIPTION};
        }

        if (property_exists($invoice, PROPERTY_INVOICE_TEMPLATE_ID)) {
            $this->invoiceTemplateId = $invoice->{PROPERTY_INVOICE_TEMPLATE_ID};
        }

        if (property_exists($invoice, PROPERTY_CART)) {
            foreach ($invoice->{PROPERTY_CART} as $cart) {
                $this->cart[] = ResponseHandler::getCart($cart);
            }
        }

        if (property_exists($invoice, PROPERTY_REASON)) {
            $this->reason = $invoice->{PROPERTY_REASON};
        }
    }

}
