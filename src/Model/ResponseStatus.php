<?php

namespace App\Model;

enum ResponseStatus
{
    case SUCCESS;
    case NOMESSAGE_SPECIFIED;

    case UNKNOW_ERROR;
    case INVALID_KEY;


    public function message(string $param = ""): string
    {
        return match ($this) {
            ResponseStatus::SUCCESS => 'Success',
            ResponseStatus::NOMESSAGE_SPECIFIED => 'No message specified',
            ResponseStatus::UNKNOW_ERROR => 'Unknown error'
        };
    }

    public function code(): string
    {
        return match ($this) {
            ResponseStatus::SUCCESS => '200',
            ResponseStatus::NOMESSAGE_SPECIFIED => '300',
            ResponseStatus::UNKNOW_ERROR => '500'
        };
    }
}