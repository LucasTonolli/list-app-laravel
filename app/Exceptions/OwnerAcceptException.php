<?php

namespace App\Exceptions;

class OwnerAcceptException extends InvitationException
{
    public function __construct(string $message = 'Usuário proprietário da lista.')
    {
        parent::__construct($message);
    }
}
