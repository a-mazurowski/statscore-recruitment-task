<?php

namespace App\Exception;

use Throwable;

class UndefinedEventException extends \InvalidArgumentException
{
    public function __construct(?string $type = null, int $code = 0, ?Throwable $previous = null)
    {
        $message = $type ? sprintf('Event "%s" is not defined', $type) : 'Event type is required';

        parent::__construct($message, $code, $previous);
    }
}