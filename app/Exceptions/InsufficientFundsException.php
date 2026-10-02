<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientFundsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('موجودی کیف پول کافی نیست.');
    }
}