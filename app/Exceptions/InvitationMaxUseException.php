<?php

namespace App\Exceptions;

class InvitationMaxUseException extends InvitationException
{
    public function __construct(string $message = 'Compartilhamento excedido.')
    {
        parent::__construct($message);
    }
}
