<?php

namespace src\Helpers;

class Log
{

    /**
     * @var string
     */
    private $requestUrl;

    /**
     * @var string
     */
    private $requestMethod;

    /**
     * @var string
     */
    private $requestHeaders;

    /**
     * @var string | null
     */
    private $requestBody;

    /**
     * @var int | null
     */
    private $responseCode;

    /**
     * @var string
     */
    private $responseBody;

    /**
     * @var string
     */
    private $responseHeaders;

    /**
     * @param string $requestUrl
     * @param string $requestMethod
     * @param string $requestHeaders
     * @param string $responseBody
     * @param string $responseHeaders
     */
    public function __construct(
        $requestUrl,
        $requestMethod,
        $requestHeaders,
        $responseBody,
        $responseHeaders
    ) {
        $this->requestUrl = $requestUrl;
        $this->requestMethod = $requestMethod;
        $this->requestHeaders = $requestHeaders;
        $this->responseBody = $responseBody;
        $this->responseHeaders = $responseHeaders;
    }

    /**
     * @param int $responseCode
     *
     * @return $this
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * @param string $requestBody
     *
     * @return $this
     */
    public function setRequestBody($requestBody)
    {
        $this->requestBody = $requestBody;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];

        foreach ($this as $property => $value) {
            if (!empty($value)) {
                $result[$property] = $value;
            }
        }

        return $result;
    }

}
