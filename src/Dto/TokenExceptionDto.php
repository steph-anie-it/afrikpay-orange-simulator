<?php

namespace App\Dto;

class TokenExceptionDto
{
    public function __construct(
        public ?string $error_description = null,
        public ?string $error = null
    )
    {
    }
}