<?php

namespace App\Modules\Purchasing\Exceptions;

use Exception;

/**
 * Una compra ya recibida no admite edición.
 */
final class PurchaseNotEditableException extends Exception
{
    public function __construct()
    {
        parent::__construct('La compra ya fue recibida y no se puede editar.');
    }
}
