<?php

namespace src\Api\Tokens\CreatePaymentResource\Request;

use src\Api\Exceptions\WrongDataException;

/**
 * Провайдер платежного терминала
 */
class TerminalProvider
{

    const EUROSET = 'euroset';

    /**
     * Допустимые значения провайдера платежного терминала
     */
    private $validValues = [
        self::EUROSET,
    ];

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     *
     * @throws WrongDataException
     */
    public function __construct($value)
    {
        if (!in_array($value, $this->validValues)) {
            throw new WrongDataException(getLabel('RBK_MONEY_WRONG_VALUE') . ' `terminalProvider`', RBK_MONEY_HTTP_CODE_BAD_REQUEST);
        }

        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

}
