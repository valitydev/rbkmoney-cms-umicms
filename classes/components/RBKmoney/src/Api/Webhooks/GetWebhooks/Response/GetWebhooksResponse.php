<?php

namespace src\Api\Webhooks\GetWebhooks\Response;

use src\Api\Exceptions\WrongDataException;
use src\Api\Interfaces\ResponseInterface;
use src\Api\RBKmoneyDataObject;
use src\Api\Webhooks\WebhookResponse\WebhookResponse;

class GetWebhooksResponse extends RBKmoneyDataObject implements ResponseInterface
{

    /**
     * @var array | WebhookResponse[]
     */
    public $webhooks = [];

    /**
     * @param array $responseObject
     *
     * @throws WrongDataException
     */
    public function __construct(array $responseObject)
    {
        foreach ($responseObject as $response) {
            $this->webhooks[] = new WebhookResponse($response);
        }
    }

}
