<?php

namespace src\Api\Invoices\InvoiceResponse;

use src\Api\Invoices\CreateInvoice\Cart;

/**
 * Корзина с набором позиций продаваемых товаров или услуг
 */
class CartResponse extends Cart
{

    /**
     * Суммарная стоимость позиции с учётом количества единиц товаров или услуг
     *
     * @var int
     */
    protected $cost;

    /**
     * @param int $cost
     *
     * @return CartResponse
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

}