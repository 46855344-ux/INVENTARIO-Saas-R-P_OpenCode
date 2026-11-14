<?php

namespace App\Modules\Inventory\Exceptions;

use Exception;

/**
 * El movimiento ya tiene una reversión registrada.
 */
final class MovementAlreadyReversedException extends Exception
{
    public function __construct()
    {
        parent::__construct('El movimiento ya fue revertido.');
    }
}
