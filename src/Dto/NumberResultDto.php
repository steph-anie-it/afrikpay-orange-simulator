<?php

namespace App\Dto;

use App\Entity\Number;

class NumberResultDto
{
    public ?string $numberId;

    public ?string $numberNumber;

    public ?string $airtimeBalance;

    public function __construct(Number $number){
        $this->numberNumber = $number->getNumberNumber();
        $this->airtimeBalance = $number->getAirtimeBalance();
    }

}