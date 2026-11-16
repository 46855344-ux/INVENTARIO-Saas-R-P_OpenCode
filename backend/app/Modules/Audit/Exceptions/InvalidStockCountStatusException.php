<?php

namespace App\Modules\Audit\Exceptions;

use Exception;

final class InvalidStockCountStatusException extends Exception
{
    public function __construct()
    {
        parent::__construct('El estado del conteo no permite la operación solicitada.');
    }
}
