<?php

namespace App\Modules\Purchasing\Exceptions;

use Exception;

/**
 * La clave de idempotencia ya pertenece a otra operación.
 */
final class IdempotencyConflictException extends Exception
{
    public function __construct()
    {
        parent::__construct('La clave de idempotencia ya fue utilizada en otra operación.');
    }
}
