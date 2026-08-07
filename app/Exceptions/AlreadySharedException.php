<?php

namespace App\Exceptions;

class AlreadySharedException extends InvitationException
{
    public function __construct(string $message = 'Usuário já compartilha essa lista.')
    {
        parent::__construct($message);
    }
}
