<?php

namespace App\Exceptions;

abstract class OrderException extends \Exception
{
    protected $code = 400;

    abstract public function toArray(): array;
}
