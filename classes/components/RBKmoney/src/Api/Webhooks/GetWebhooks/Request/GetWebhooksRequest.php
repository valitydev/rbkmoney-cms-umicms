<?php

namespace src\Api\Webhooks\GetWebhooks\Request;

use src\Api\Interfaces\GetRequestInterface;
use src\Api\RBKmoneyDataObject;

class GetWebhooksRequest extends RBKmoneyDataObject implements GetRequestInterface
{

    const PATH = '/processing/webhooks';

    /**
     * @return string
     */
    public function getPath()
    {
        return self::PATH;
    }

}
