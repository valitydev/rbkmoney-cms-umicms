<?php

namespace src\Api\Payments\PaymentResponse;

use src\Api\RBKmoneyDataObject;

/**
 * Параметры созданного платежа
 */
abstract class Flow extends RBKmoneyDataObject
{

    const HOLD = 'PaymentFlowHold';
    const INSTANT = 'PaymentFlowInstant';

    /**
     * Тип процесса выполнения платежа
     *
     * @var string
     */
    public $type;

    /**
     * @return array
     */
    public function toArray()
    {
        $properties = [];

        foreach ($this as $property => $value) {
            if (!empty($value)) {
                $properties[$property] = (is_object($value)) ? $value->getValue() : $value;
            }
        }

        return $properties;
    }
}
