<?php

namespace src\Client;

use src\Api\Customers\CreateCustomer\Request\CreateCustomerRequest;
use src\Api\Customers\CreateCustomer\Response\CreateCustomerResponse;
use src\Api\Exceptions\WrongDataException;
use src\Api\Exceptions\WrongRequestException;
use src\Api\Invoices\GetInvoiceById\Response\GetInvoiceByIdResponse;
use src\Api\Invoices\GetInvoiceById\Request\GetInvoiceByIdRequest;
use src\Api\Payments\CancelPayment\Request\CancelPaymentRequest;
use src\Api\Payments\CapturePayment\Request\CapturePaymentRequest;
use src\Api\Payments\CreatePayment\Response\CreatePaymentResponse;
use src\Api\Payments\CreateRefund\Request\CreateRefundRequest;
use src\Api\Payments\RefundResponse\RefundResponse;
use src\Api\Search\SearchPayments\Request\SearchPaymentsRequest;
use src\Api\Search\SearchPayments\Response\SearchPaymentsResponse;
use src\Api\Tokens\CreatePaymentResource\Request\CreatePaymentResourceRequest;
use src\Api\Tokens\CreatePaymentResource\Response\CreatePaymentResourceResponse;
use src\Api\Webhooks\CreateWebhook\Request\CreateWebhookRequest;
use src\Api\Webhooks\CreateWebhook\Response\CreateWebhookResponse;
use src\Api\Webhooks\GetWebhooks\Request\GetWebhooksRequest;
use src\Api\Webhooks\GetWebhooks\Response\GetWebhooksResponse;
use src\Exceptions\RequestException;
use src\Interfaces\ClientInterface;
use src\Api\Invoices\CreateInvoice\Request\CreateInvoiceRequest;
use src\Api\Payments\CreatePayment\Request\CreatePaymentRequest;
use src\Api\Invoices\CreateInvoice\Response\CreateInvoiceResponse;

class Sender
{

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param CreateInvoiceRequest $request
     *
     * @return CreateInvoiceResponse
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function sendCreateInvoiceRequest(CreateInvoiceRequest $request)
    {
        $response = $this->client->sendRequest($request, ClientInterface::HTTP_METHOD_POST);

        return new CreateInvoiceResponse(json_decode($response));
    }

    /**
     * @param CreatePaymentRequest $request
     *
     * @return CreatePaymentResponse
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function sendCreatePaymentRequest(CreatePaymentRequest $request)
    {
        $response = $this->client->sendRequest($request, ClientInterface::HTTP_METHOD_POST);

        return new CreatePaymentResponse(json_decode($response));
    }

    /**
     * @param CreateRefundRequest $request
     *
     * @return RefundResponse
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function sendCreateRefundRequest(CreateRefundRequest $request)
    {
        $response = $this->client->sendRequest($request, ClientInterface::HTTP_METHOD_POST);

        return new RefundResponse(json_decode($response));
    }

    /**
     * @param GetInvoiceByIdRequest $request
     *
     * @return GetInvoiceByIdResponse
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function sendGetInvoiceByIdRequest(GetInvoiceByIdRequest $request)
    {
        $response = $this->client->sendRequest($request, ClientInterface::HTTP_METHOD_GET);

        return new GetInvoiceByIdResponse(json_decode($response));
    }

    /**
     * @param CreateWebhookRequest $request
     *
     * @return CreateWebhookResponse
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function sendCreateWebhookRequest(CreateWebhookRequest $request)
    {
        $response = $this->client->sendRequest($request, ClientInterface::HTTP_METHOD_POST);

        return new CreateWebhookResponse(json_decode($response));
    }

    /**
     * @param GetWebhooksRequest $request
     *
     * @return GetWebhooksResponse
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function sendGetWebhooksRequest(GetWebhooksRequest $request)
    {
        $response = $this->client->sendRequest($request, ClientInterface::HTTP_METHOD_GET);

        return new GetWebhooksResponse(json_decode($response));
    }

    /**
     * @param CancelPaymentRequest $request
     *
     * @return void
     *
     * @throws RequestException
     * @throws WrongRequestException
     */
    public function sendCancelPaymentRequest(CancelPaymentRequest $request)
    {
        $this->client->sendRequest($request, ClientInterface::HTTP_METHOD_POST);
    }

    /**
     * @param CapturePaymentRequest $request
     *
     * @return void
     *
     * @throws RequestException
     * @throws WrongRequestException
     */
    public function sendCapturePaymentRequest(CapturePaymentRequest $request)
    {
        $this->client->sendRequest($request, ClientInterface::HTTP_METHOD_POST);
    }

    /**
     * @param SearchPaymentsRequest $request
     *
     * @return SearchPaymentsResponse
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function sendSearchPaymentsRequest(SearchPaymentsRequest $request)
    {
        $response = $this->client->sendRequest($request, ClientInterface::HTTP_METHOD_GET);

        return new SearchPaymentsResponse(json_decode($response));
    }

    /**
     * @param CreatePaymentResourceRequest $request
     *
     * @return CreatePaymentResourceResponse
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function sendCreatePaymentResourceRequest(CreatePaymentResourceRequest $request) {
        $response = $this->client->sendRequest($request, ClientInterface::HTTP_METHOD_POST);

        return new CreatePaymentResourceResponse(json_decode($response));
    }

    /**
     * @param CreateCustomerRequest $request
     *
     * @return CreateCustomerResponse
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function sendCreateCustomerRequest(CreateCustomerRequest $request)
    {
        $response = $this->client->sendRequest($request, ClientInterface::HTTP_METHOD_POST);

        return new CreateCustomerResponse(json_decode($response));
    }

}
