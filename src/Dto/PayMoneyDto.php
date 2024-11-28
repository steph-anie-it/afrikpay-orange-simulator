<?php

namespace App\Dto;

class PayMoneyDto
{
  public function __construct(
      public ?string $payToken = null,
      public ?string $description = null,
      public ?string $subscriberMsisdn= null,
      public ?string $pin = null,
      public ?string $orderId = null,
      public string|float|null $amount = null,
      public ?string $channelUserMsisdn = null,
      public ?string $notifUrl = null,
  )
  {
  }
}