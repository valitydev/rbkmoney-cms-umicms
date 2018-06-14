<?php

namespace src\Helpers;

use src\Api\ContactInfo;
use src\Api\Error;
use src\Api\Exceptions\WrongDataException;
use src\Api\Invoices\CreateInvoice\Cart;
use src\Api\Invoices\CreateInvoice\TaxMode;
use src\Api\Invoices\InvoiceResponse\CartResponse;
use src\Api\Payments\CreatePayment\HoldType;
use src\Api\Payments\PaymentResponse\ClientInfo;
use src\Api\Payments\PaymentResponse\CustomerPayer;
use src\Api\Payments\PaymentResponse\DetailsBankCard;
use src\Api\Payments\PaymentResponse\DetailsDigitalWallet;
use src\Api\Payments\PaymentResponse\DetailsPaymentTerminal;
use src\Api\Payments\PaymentResponse\Flow;
use src\Api\Payments\PaymentResponse\FlowHold;
use src\Api\Payments\PaymentResponse\FlowInstant;
use src\Api\Payments\PaymentResponse\Payer;
use src\Api\Payments\PaymentResponse\PaymentResourcePayer;
use src\Api\Payments\PaymentResponse\PaymentSystem;
use src\Api\Payments\PaymentResponse\PaymentToolDetails;
use stdClass;

/**
 * Обрабатывает ответы RBKmoney
 */
class ResponseHandler
{

    /**
     * @param stdClass $error
     *
     * @return Error
     */
    public static function getError(stdClass $error)
    {
        return new Error($error->code, $error->message);
    }

    /**
     * @param stdClass $flow
     *
     * @return Flow
     *
     * @throws WrongDataException
     */
    public static function getFlow(stdClass $flow)
    {
        if (Flow::HOLD === $flow->type) {
            $flowHold = new FlowHold(new HoldType($flow->onHoldExpiration));

            return $flowHold->setHeldUntil($flow->heldUntil);
        }

        return new FlowInstant();
    }

    /**
     * @param stdClass $payer
     *
     * @return Payer
     *
     * @throws WrongDataException
     */
    public static function getPayer(stdClass $payer)
    {
        if (Payer::CUSTOMER_PAYER === $payer->payerType) {
            return new CustomerPayer($payer->customerID);
        }

        $resourcePayer = new PaymentResourcePayer(
            $payer->paymentToolToken,
            $payer->paymentSession,
            self::getContactInfo($payer->contactInfo)
        );

        if (property_exists($payer, PROPERTY_PAYMENT_TOOL_DETAILS)) {
            $resourcePayer->setPaymentToolDetails(self::getPaymentToolDetails($payer->{PROPERTY_PAYMENT_TOOL_DETAILS}));
        }

        if (property_exists($payer, PROPERTY_CLIENT_INFO)) {
            $resourcePayer->setClientInfo(self::getClientInfo($payer->{PROPERTY_CLIENT_INFO}));
        }

        return $resourcePayer;
    }

    /**
     * @param stdClass $info
     *
     * @return ContactInfo
     * @throws WrongDataException
     */
    public static function getContactInfo(stdClass $info)
    {
        $contactInfo = new ContactInfo();

        if (property_exists($info, PROPERTY_PHONE_NUMBER)) {
            $contactInfo->setPhone($info->{PROPERTY_PHONE_NUMBER});
        }

        if (property_exists($info, PROPERTY_EMAIL)) {
            $contactInfo->setEmail($info->{PROPERTY_EMAIL});
        }

        return $contactInfo;
    }

    /**
     * @param stdClass $details
     *
     * @return PaymentToolDetails
     *
     * @throws WrongDataException
     */
    public static function getPaymentToolDetails(stdClass $details)
    {
        if (PaymentToolDetails::DIGITAL_WALLET === $details->detailsType) {
            return new DetailsDigitalWallet($details->digitalWalletDetailsType);
        } elseif (PaymentToolDetails::PAYMENT_TERMINAL === $details->detailsType) {
            return new DetailsPaymentTerminal($details->provider);
        }

        return new DetailsBankCard(
            $details->cardNumberMask,
            new PaymentSystem($details->paymentSystem)
        );
    }

    /**
     * @param stdClass $info
     *
     * @return ClientInfo
     */
    public static function getClientInfo(stdClass $info)
    {
        $clientInfo = new ClientInfo($info->fingerprint);

        if (property_exists($info, PROPERTY_IP)) {
            $clientInfo->setIp($info->{PROPERTY_IP});
        }

        return $clientInfo;
    }

    /**
     * @param stdClass $invoiceCart
     *
     * @return Cart
     *
     * @throws WrongDataException
     */
    public static function getCart(stdClass $invoiceCart)
    {
        $cart = new CartResponse(
            $invoiceCart->product,
            $invoiceCart->quantity,
            $invoiceCart->price
        );

        if (property_exists($invoiceCart, PROPERTY_TAX_MODE)) {
            $cart->setTaxMode(new TaxMode($invoiceCart->{PROPERTY_TAX_MODE}->rate));
        }

        if (property_exists($invoiceCart, PROPERTY_COST)) {
            $cart->setCost($invoiceCart->cost);
        }

        return $cart;
    }

}
