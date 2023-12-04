<?php

namespace App\Exception;

use App\Entity\Transaction;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;
use Throwable;


#[WithHttpStatus(Response::HTTP_BAD_REQUEST)]
class ParameterNotFoundException extends  GeneralException
{
    public const MESSAGE="%s %s not found.";
    public function __construct(string $message = "",Transaction $transaction = null, int $code = 500, ?Throwable $previous = null)
    {
        $values = explode(",",$message);
        $params = count($values) > 0 ?  strtoupper($values[0]) : "";
        $value = count($values) > 1 ? $values[1] : "";
        $this->transaction = $transaction;
        $message = sprintf(self::MESSAGE,$params,$value);
        parent::__construct($message,$transaction, $code, $previous);
    }
}