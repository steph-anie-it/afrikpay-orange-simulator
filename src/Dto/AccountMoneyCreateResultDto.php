<?php

namespace App\Dto;

class AccountMoneyCreateResultDto
{
    public string $username;
    public string $apikey;

    public string $msisdn;

    public ?string $pin = null;

}