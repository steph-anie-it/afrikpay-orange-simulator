<?php

namespace App\Model;

enum ResponseStatus
{
    case SUCCESS;
    case NOMESSAGE_SPECIFIED;

    case UNKNOW_ERROR;
    case INVALID_KEY;

    case BAD_PIN_NUMBER;


    case INVALID_CREDENTIAL;


    case ACCOUNT_NOT_FOUND;


    case INVALID_PHONE_NUMBER;


    case INSUFFICIENT_BALANCE_NUMBER;

    public function message(string $param = ""): string
    {
        return match ($this) {
            ResponseStatus::SUCCESS => 'Success',
            ResponseStatus::NOMESSAGE_SPECIFIED => 'No message specified',
            ResponseStatus::UNKNOW_ERROR => 'Unknown error',
            ResponseStatus::BAD_PIN_NUMBER => 'Bad pin number',
            ResponseStatus::INVALID_CREDENTIAL => 'Invalid Credentials',
            ResponseStatus::ACCOUNT_NOT_FOUND => 'Account not found',
            ResponseStatus::INVALID_PHONE_NUMBER => 'Invalid phone number for account %s',
            ResponseStatus::INSUFFICIENT_BALANCE_NUMBER => 'Insufficient Balance'
        };
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
            ResponseStatus::INSUFFICIENT_BALANCE_NUMBER => '402'
        };
    }

}