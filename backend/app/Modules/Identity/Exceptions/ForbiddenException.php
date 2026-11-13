<?php

namespace App\Modules\Identity\Exceptions;

use Exception;

/**
 * El usuario está autenticado, pero su rol no autoriza la acción.
 */
final class ForbiddenException extends Exception
{
    public function __construct(string $message = 'No tiene permiso para realizar esta acción.')
    {
        parent::__construct($message);
    }
}
