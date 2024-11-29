<?php

namespace App\Dto;

class AccountMoneyCreateDto
{

    public function __construct(
        public ?string $username = null,
        public ?string $password = null
    )
    {

    }
}