<?php

namespace App\Exception;

use App\Dto\PayMoneyDataResultDto;
use App\Model\ResponseStatus;

class MoneyPayException extends  GeneralException
{
    public const MESSAGE="%s::%s";
    public $payResultDto = null;
    public $messageCode = null;
    public $clearMessage = null;
    public function __construct(string $message = "", int $code = 200,array $exceptionValues = [],PayMoneyDataResultDto $payMoneyDataResultDto = null)
    {
        $this->payResultDto = $payMoneyDataResultDto;
        $this->messageCode = $exceptionValues[ExceptionList::CODE];
        $this->clearMessage = $exceptionValues[ExceptionList::MESSAGE];
        parent::__construct($message,null,ResponseStatus::UNKNOW_ERROR,$code);
    }
}