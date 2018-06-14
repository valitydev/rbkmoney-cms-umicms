<?php

namespace src\Api\Interfaces;

interface PostRequestInterface extends RequestInterface
{

    /**
     * @return array
     */
    public function toArray();

    /**
     * @return string
     */
    public function getPath();

}
