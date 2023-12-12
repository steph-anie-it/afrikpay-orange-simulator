<?php

namespace App\Exception;

use App\Entity\Transaction;
use App\Model\ResponseStatus;
use Throwable;

class GeneralException extends  \Exception
{
    public ?Transaction $transaction = null;
    private ResponseStatus $responseStatus;

    public function __construct(string $message = null,
                                Transaction $transaction = null,
                                ResponseStatus $responseStatus = ResponseStatus::UNKNOW_ERROR,
                                int $code = 500, ?Throwable $previous = null)
    {
        $this->transaction = $transaction;
        $this->responseStatus = $responseStatus;
        $message = $message ?? "";
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return Transaction
     */
    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    /**
     * @return ResponseStatus
     */
    public function getResponseStatus(): ResponseStatus
    {
        return $this->responseStatus;
    }

}