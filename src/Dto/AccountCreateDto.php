<?php

namespace App\Dto;

class AccountCreateDto
{

    public function __construct(
        public ?string $username = null,
        public ?string $password = null,
        public string $operationtype = 'airtime'
    )
    {

    }
}