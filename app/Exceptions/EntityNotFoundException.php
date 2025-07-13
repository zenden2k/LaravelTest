<?php

namespace App\Exceptions;

class EntityNotFoundException extends \Exception
{
    protected $message = 'Сущность не найдена';
    protected $code = 404;
}
