<?php

namespace App\Exceptions;

use Exception;

class ProductNotAvailableException extends Exception
{
    public function __construct(string $message = "Product is not available for purchase")
    {
        parent::__construct($message);
    }
}
