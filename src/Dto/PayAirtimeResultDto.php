<?php

namespace App\Dto;

class PayAirtimeResultDto
{   public string $error_code;
    public string $status;
    public PayAirtimeTransactionResultDto $transaction;

}