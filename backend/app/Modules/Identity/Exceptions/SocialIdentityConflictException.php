<?php

namespace App\Modules\Identity\Exceptions;

use Exception;

/**
 * La identidad externa ya pertenece a otro usuario, o el usuario ya tiene una
 * identidad de ese proveedor.
 */
final class SocialIdentityConflictException extends Exception
{
    public function __construct()
    {
        parent::__construct('La identidad social ya está vinculada a otra cuenta.');
    }
}
