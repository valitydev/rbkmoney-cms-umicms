<?php

namespace src\Api\Exceptions;

use src\Exceptions\RBKmoneyException;

/**
 * Выбрасывается в случае передачи невалидных данных в клиент
 */
class WrongRequestException extends RBKmoneyException
{

}
