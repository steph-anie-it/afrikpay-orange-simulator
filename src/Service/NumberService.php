<?php

namespace App\Service;

use App\Dto\AccountCreateDto;
use App\Dto\AccountCreateResultDto;
use App\Dto\CommandHeaderDto;
use App\Dto\GenerateNumberDto;
use App\Dto\GenerateNumberResultDto;
use App\Dto\PayAirtimeDto;
use App\Dto\PayAirtimeFullDto;
use App\Dto\PayAirtimeResultDto;
use App\Dto\PayDataFullDto;
use App\Dto\Result\CommandResultDto;
use App\Dto\TransactionStatusFullDto;
use App\Entity\Transaction;

interface NumberService
{
    public function generateNumber() : GenerateNumberResultDto;

    public function payAirtime(PayAirtimeFullDto $payAirtimeFullDto):CommandResultDto;

    public function check(PayAirtimeDto $payAirtimeDto);

    public function checkAccount(CommandHeaderDto $header,Transaction $transaction=null);

    public function createAirtimeAccount(AccountCreateDto $createDto) : AccountCreateResultDto;

    public function payData(PayDataFullDto $param):CommandResultDto;


    public function transactionStatus(TransactionStatusFullDto $param):CommandResultDto;

}