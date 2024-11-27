<?php

namespace App\Dto;

class PayMoneyResultDto
{
  public function __construct(
      public ?PayMoneyDataResultDto $data = null,
      public ?string $message = null
  )
  {
  }
}