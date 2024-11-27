<?php

namespace App\Dto;

class TokenDto
{
   public function __construct(
       public ?string $access_token = null,
       public ?string $refresh_token = null,
       public ?string $scope = "default",
       public ?string $token_type = "Bearer",
       public ?int $expires_in = 3600
   )
   {

   }
}