<?php

namespace App\Modules\Inventory\Exceptions;

use Exception;

/**
 * El tipo de movimiento no admite reversión.
 */
final class MovementNotReversibleException extends Exception
{
    public function __construct()
    {
        parent::__construct('El movimiento indicado no se puede revertir.');
    }
}
