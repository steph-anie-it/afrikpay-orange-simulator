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
        $messageValue = $exceptionValues[ExceptionList::MESSAGE];
        $this->clearMessage = !empty($message) ?
            sprintf($messageValue,$message)
            : $messageValue;
        parent::__construct($message,null,ResponseStatus::UNKNOW_ERROR,$code);
    }
}