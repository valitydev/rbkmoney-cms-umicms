<?php

namespace src\Api;

/**
 * Описание ошибки, возникшей в процессе проведения платежа
 */
class Error extends RBKmoneyDataObject
{

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $message;

    /**
     * @param string $code
     * @param string $message
     */
    public function __construct($code, $message)
    {
        $this->code = $code;
        $this->message = $message;
    }

}
