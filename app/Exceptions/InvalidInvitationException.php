<?php

namespace App\Exceptions;

class InvalidInvitationException extends InvitationException
{
    public function __construct(string $message = 'Convite inválido.')
    {
        parent::__construct($message);
    }
}
