<?php

namespace src\Api;

abstract class RBKmoneyDataObject
{

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name) {
        return property_exists(get_called_class(), $name);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name) {
        if ($this->__isset($name)) {
            return $this->$name;
        }
    }

    /**
     * Метод объявлен только для того, чтоб
     * запретить динамически создавать поля объекта
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value) {
        // Реализация не предполагается
    }

}
