<?php

namespace App\Exception;

use App\Entity\Transaction;
use App\Model\ResponseStatus;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;
use Throwable;


#[WithHttpStatus(Response::HTTP_UNAUTHORIZED)]
class InvalidMoneyCredentialsException extends  GeneralException
{
    public const MESSAGE="Invalid credentials for account";
    public function __construct(string $message = "",Transaction $transaction = null, int $code = 500, ?Throwable $previous = null)
    {
        $this->transaction = $transaction;
        $message = sprintf(self::MESSAGE,$message);
        parent::__construct($message,$transaction,ResponseStatus::INVALID_CREDENTIAL);
    }
}