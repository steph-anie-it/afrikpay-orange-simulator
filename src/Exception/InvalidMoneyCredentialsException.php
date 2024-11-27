<?php

namespace App\Exception;

use App\Dto\TokenExceptionDto;
use App\Entity\Transaction;
use App\Model\ResponseStatus;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;
use Throwable;


#[WithHttpStatus(Response::HTTP_UNAUTHORIZED)]
class InvalidMoneyCredentialsException extends  GeneralException
{
    public const MESSAGE="%s::%s";
    public $messageCode = null;
    public $clearMessage = null;
    public function __construct(string $message = "", int $code = 200,array $exceptionValues = [])
    {
        $this->messageCode = $exceptionValues[ExceptionList::CODE];
        $this->clearMessage = $exceptionValues[ExceptionList::MESSAGE];
        parent::__construct($message,null,ResponseStatus::UNKNOW_ERROR,$code);
    }
}