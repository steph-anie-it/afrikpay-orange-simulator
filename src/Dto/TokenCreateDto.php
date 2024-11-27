<?php

namespace App\Dto;

class TokenCreateDto
{
        public function __construct(
            public ?string $username = null,
            public ?string $password = null,
            public ?string $grant_type = null
        )
        {
        }
}