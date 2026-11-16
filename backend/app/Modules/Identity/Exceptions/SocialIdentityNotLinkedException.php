<?php

namespace App\Modules\Identity\Exceptions;

use Exception;

/**
 * La identidad social es válida pero no está vinculada a un usuario interno.
 */
final class SocialIdentityNotLinkedException extends Exception
{
    public function __construct()
    {
        parent::__construct('La cuenta social no está vinculada a un usuario del sistema.');
    }
}
