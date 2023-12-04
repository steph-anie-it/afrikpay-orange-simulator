<?php

namespace App\Exception;

use App\Entity\Transaction;
use Throwable;

class GeneralException extends  \Exception
{
    public ?Transaction $transaction = null;

    public function __construct(string $message = "",Transaction $transaction = null, int $code = 500, ?Throwable $previous = null)
    {
        $this->transaction = $transaction;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return Transaction
     */
    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

}