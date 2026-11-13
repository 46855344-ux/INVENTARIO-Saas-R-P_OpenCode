<?php

namespace App\Modules\Inventory\Exceptions;

use Exception;

/**
 * No existe un factor de conversión activo entre las unidades indicadas.
 */
final class ConversionNotDefinedException extends Exception
{
    public function __construct()
    {
        parent::__construct('No existe una conversión activa entre las unidades indicadas.');
    }
}
