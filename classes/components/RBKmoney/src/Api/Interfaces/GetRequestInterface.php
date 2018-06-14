<?php

namespace src\Api\Interfaces;

interface GetRequestInterface extends RequestInterface
{

    /**
     * @return string
     */
    public function getPath();

}
