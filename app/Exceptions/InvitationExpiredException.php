<?php

namespace App\Exceptions;

class InvitationExpiredException extends InvitationException
{
    public function __construct(string $message = 'Link de compartilhamento expirado.')
    {
        parent::__construct($message);
    }
}
