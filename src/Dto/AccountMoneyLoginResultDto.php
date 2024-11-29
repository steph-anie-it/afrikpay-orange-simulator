<?php

namespace App\Dto;

class AccountMoneyLoginResultDto
{
    public string $username;

    public string $msisdn;

    public ?string $pin = null;

}