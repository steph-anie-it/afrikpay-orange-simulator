<?php

namespace App\Model;

enum ResponseStatus
{
    case SUCCESS;
    case NOMESSAGE_SPECIFIED;

    case UNKNOW_ERROR;

    case INVALID_HEADER;
    case INVALID_KEY;

    case BAD_PIN_NUMBER;

    case INVALID_CREDENTIAL;


    case ACCOUNT_NOT_FOUND;


    case ACCOUNT_ALREADY_EXISTS;

    case INVALID_PHONE_NUMBER;


    case INSUFFICIENT_BALANCE_NUMBER;

    case INVALID_PARAMETER;

    public function message(string $param = ""): string
    {
        return match ($this) {
            ResponseStatus::SUCCESS => 'Success',
            ResponseStatus::NOMESSAGE_SPECIFIED => 'No message specified',
            ResponseStatus::UNKNOW_ERROR => 'Unknown error',
            ResponseStatus::BAD_PIN_NUMBER => 'Bad pin number',
            ResponseStatus::INVALID_CREDENTIAL => 'Invalid Credentials for %s',
            ResponseStatus::ACCOUNT_NOT_FOUND => 'Account %s not found',
            ResponseStatus::INVALID_PHONE_NUMBER => 'Invalid phone number for account %s',
            ResponseStatus::INSUFFICIENT_BALANCE_NUMBER => 'Insufficient Balance',
            ResponseStatus::ACCOUNT_ALREADY_EXISTS => 'Account already exits',
            ResponseStatus::INVALID_HEADER => 'Invalid header',
            ResponseStatus::INVALID_PARAMETER => 'Invalid %s parameter %s'
        };
    }

    public function getMessage(mixed ...$message):string
    {
        $value = $message[0];
        $value = array_map('strval', explode(',', $value));
        return sprintf($this->message(),...$value);
    }

    public function code(): string
    {
        return match ($this) {
            ResponseStatus::SUCCESS => '200',
            ResponseStatus::NOMESSAGE_SPECIFIED => '300',
            ResponseStatus::UNKNOW_ERROR => '500',
            ResponseStatus::BAD_PIN_NUMBER => '501',
            ResponseStatus::INVALID_CREDENTIAL => '401',
            ResponseStatus::ACCOUNT_NOT_FOUND => '404',
            ResponseStatus::INVALID_PHONE_NUMBER => '403',
            ResponseStatus::INSUFFICIENT_BALANCE_NUMBER => '402',
            ResponseStatus::ACCOUNT_ALREADY_EXISTS => '405',
            ResponseStatus::INVALID_HEADER => '406',
            ResponseStatus::INVALID_PARAMETER => '407'
        };
    }

}