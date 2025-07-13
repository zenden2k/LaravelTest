<?php

namespace App\Exceptions;

abstract class ApiException extends \Exception
{
    protected $code = 400;

    abstract public function toArray(): array;
}
