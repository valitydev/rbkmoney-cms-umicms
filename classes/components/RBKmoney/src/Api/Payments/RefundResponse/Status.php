<?php

namespace src\Api\Payments\RefundResponse;

use src\Api\Exceptions\WrongDataException;

/**
 * Статус возврата
 */
class Status
{

    const PENDING = 'pending';
    const SUCCEEDED = 'succeeded';
    const FAILED = 'failed';

    /**
     * Допустимые значения статуса возврата
     */
    private $validValues = [
        self::PENDING,
        self::SUCCEEDED,
        self::FAILED,
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
            throw new WrongDataException(getLabel('RBK_MONEY_WRONG_VALUE') . ' `status`', RBK_MONEY_HTTP_CODE_BAD_REQUEST);
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
