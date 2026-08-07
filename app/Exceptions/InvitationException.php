<?php

namespace App\Exceptions;

use Exception;

abstract class InvitationException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message, 409);
    }
}
