<?php

namespace App\Modules\Inventory\Exceptions;

use Exception;

/**
 * Una salida o ajuste dejaría la existencia en negativo.
 */
final class InsufficientStockException extends Exception
{
    public function __construct()
    {
        parent::__construct('La existencia disponible no alcanza para confirmar la operación.');
    }
}
