<?php

namespace App\Service;

use App\Dto\AccountCreateDto;
use App\Dto\AccountCreateResultDto;
use App\Dto\AccountMoneyCreateResultDto;
use App\Dto\InitMoneyResultDto;
use App\Dto\PayMoneyDto;
use App\Dto\PayMoneyResultDto;
use App\Dto\PayTokenDto;

interface MoneyService
{
  public const WSO2_AUTHORIZATION  = 'WSO2-Authorization';
  public const X_AUTH_TOKEN = 'X-AUTH-TOKEN';
  public function init(?string $key = null) :InitMoneyResultDto;
  public function pay(PayMoneyDto $payMoneyDto, ?string $key = null):PayMoneyResultDto;

  public function generatePayToken(?string $key) : PayTokenDto;

  public function checkCredentials() : void;

  public function createMoneyAccount(AccountCreateDto $createDto) : AccountMoneyCreateResultDto;
}