<?php

namespace App\Dto;

class MoneyErrorDto
{

   public function __construct(
        public ?string $code = null,
        public ?string $message = null,
        public ?string $description = null
   )
   {
   }
}