<?php

namespace src\Api\Invoices\CreateInvoice;

use src\Api\RBKmoneyDataObject;

/**
 * Корзина с набором позиций продаваемых товаров или услуг
 */
class Cart extends RBKmoneyDataObject
{

    /**
     * Описание предлагаемого товара или услуги
     *
     * @var string
     */
    public $product;

    /**
     * Количество единиц товаров или услуг, предлагаемых на продажу в этой позиции
     *
     * @var int
     */
    public $quantity;

    /**
     * Цена предлагаемого товара или услуги, в минорных денежных единицах,
     * например в копейках в случае указания российских рублей в качестве валюты
     *
     * @var int
     */
    public $price;

    /**
     * @var TaxMode | null
     */
    public $taxMode;

    /**
     * @param string         $product
     * @param int            $quantity
     * @param int            $price
     */
    public function __construct($product, $quantity, $price)
    {
        $this->product = $product;
        $this->quantity = (int) $quantity;
        $this->price = (int) $price;
    }

    /**
     * @param TaxMode $taxMode
     *
     * @return Cart
     */
    public function setTaxMode(TaxMode $taxMode)
    {
        $this->taxMode = $taxMode;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $properties = [];

        foreach ($this as $property => $value) {
            if (null !== $value) {
                $properties[$property] = $value;
            }
        }

        return $properties;
    }

}