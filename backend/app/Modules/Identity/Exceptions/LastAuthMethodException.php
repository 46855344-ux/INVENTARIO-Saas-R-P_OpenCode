<?php

namespace App\Modules\Identity\Exceptions;

use Exception;

/**
 * La desvinculación dejaría al usuario sin un método de acceso válido.
 */
final class LastAuthMethodException extends Exception
{
    public function __construct()
    {
        parent::__construct('No se puede desvincular el último método de acceso.');
    }
}
