<?php

namespace App\Modules\Inventory\Exceptions;

use Exception;

/**
 * Las unidades pertenecen a dimensiones distintas y no se pueden convertir.
 */
final class IncompatibleUnitsException extends Exception
{
    public function __construct()
    {
        parent::__construct('Las unidades pertenecen a dimensiones distintas y no son compatibles.');
    }
}
