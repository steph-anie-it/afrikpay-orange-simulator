<?php

namespace App\Dto;

class MoneyCallbackDto
{
//{"payToken":"MP2411298827A2A8D69C52619B75","status":"SUCCESSFULL","message":"Transaction completed"}

  public CONST SUCCESS_MESSAGE = "Transaction completed";
  public CONST FAILED_MESSAGE = "Transaction failed";
  public const SUCCESSFULL = "SUCCESSFULL";

  public function __construct(
      public ?string $payToken = null,
      public ?string $status = null,
      public ?string $message = self::SUCCESS_MESSAGE
  ){
  }

}