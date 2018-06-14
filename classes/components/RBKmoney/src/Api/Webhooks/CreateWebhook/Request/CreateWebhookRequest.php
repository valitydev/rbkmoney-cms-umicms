<?php

namespace src\Api\Webhooks\CreateWebhook\Request;

use src\Api\Interfaces\PostRequestInterface;
use src\Api\RBKmoneyDataObject;
use src\Api\Webhooks\WebhookScope;

class CreateWebhookRequest extends RBKmoneyDataObject implements PostRequestInterface
{

    const PATH = '/processing/webhooks';

    /**
     * Область охвата webhook'а, ограничивающая набор типов
     * событий, по которым следует отправлять оповещения
     *
     * @var WebhookScope
     */
    protected $scope;

    /**
     * URL, на который будут поступать оповещения о произошедших событиях
     *
     * @var string
     */
    protected $url;

    /**
     * @param WebhookScope $scope
     * @param string       $url
     */
    public function __construct(WebhookScope $scope, $url)
    {
        $this->scope = $scope;
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'scope' => $this->scope->toArray(),
            'url' => $this->url,
        ];
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return self::PATH;
    }

}
