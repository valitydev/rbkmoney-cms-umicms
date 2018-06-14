<?php

namespace src\Interfaces;

use src\Api\Exceptions\WrongRequestException;
use src\Api\Interfaces\RequestInterface;
use src\Exceptions\RequestException;

interface ClientInterface
{

    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_PUT = 'PUT';

    /**
     * @param RequestInterface $request
     * @param string           $method
     *
     * @return string
     * @throws RequestException
     * @throws WrongRequestException
     */
    public function sendRequest(RequestInterface $request, $method);

}
