<?php

namespace App\Service;

use App\Dto\AccountCreateDto;
use App\Dto\AccountCreateResultDto;
use App\Dto\Command;
use App\Dto\CommandHeaderDto;
use App\Dto\CommandMessage;
use App\Dto\GenerateNumberDto;
use App\Dto\GenerateNumberResultDto;
use App\Dto\PayAirtimeDto;
use App\Dto\PayAirtimeFullDto;
use App\Dto\PayAirtimeResultDto;
use App\Dto\PayDataDto;
use App\Dto\PayDataFullDto;
use App\Dto\Result\CommandResultDto;
use App\Dto\TransactionStatusFullDto;
use App\Entity\Account;
use App\Entity\Transaction;

interface NumberService
{
    public function generateNumber() : GenerateNumberResultDto;

    public function payAirtime(PayAirtimeFullDto $payAirtimeFullDto):CommandResultDto;

    public function check(PayAirtimeDto $payAirtimeDto);

    public function checkAccount(CommandHeaderDto $header,Transaction $transaction=null):Account;

    public function createAirtimeAccount(AccountCreateDto $createDto) : AccountCreateResultDto;

    public function payData(PayDataFullDto $param):CommandResultDto;

    public function payInternetData(PayDataDto $payDataDto):CommandResultDto;


    public function transactionStatus(TransactionStatusFullDto $param):CommandResultDto;

    public function listAccount():array;

    public function payNumberAirtime(string $xml,array $headers=[]):CommandResultDto;

    public function checkBalance(string $command,array $headers=[]):CommandResultDto;

    public function checkCredentials(CommandHeaderDto $header,Transaction $transaction=null): Account;

    public function newMessage(CommandMessage $commandMessage):CommandResultDto;

    public function checkConnection(mixed $headers):void;


}