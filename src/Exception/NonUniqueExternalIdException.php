<?php

namespace App\Exception;

use App\Entity\Transaction;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;
use Throwable;


#[WithHttpStatus(Response::HTTP_BAD_REQUEST)]
class NonUniqueExternalIdException extends  GeneralException
{
    public const MESSAGE="Transaction EXTREFNUM %s must be unique";
    public function __construct(string $message = "",Transaction $transaction = null, int $code = 500, ?Throwable $previous = null)
    {
        $this->transaction = $transaction;
        $message = sprintf(self::MESSAGE,$message);
        parent::__construct($message,$transaction, $code, $previous);
    }
}