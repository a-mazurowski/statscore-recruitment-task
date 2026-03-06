<?php

namespace App\Exception;

use Throwable;

class MissingDataException extends \InvalidArgumentException
{
    public function __construct(array $missingKeys, int $code = 0, ?Throwable $previous = null)
    {
        $message = sprintf('Data key/s "%s" are required', join('", "', $missingKeys));

        parent::__construct($message, $code, $previous);
    }
}