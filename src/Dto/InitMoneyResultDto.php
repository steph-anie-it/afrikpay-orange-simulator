<?php

namespace App\Dto;

class InitMoneyResultDto
{
  //cashin CI
  //cashout CO
  //mp MP

    public function __construct(
        public PayMoneyDto $data = new PayMoneyDto(),
        public ?string     $message = "Payment request successfully initiated"
    )
    {

    }
}