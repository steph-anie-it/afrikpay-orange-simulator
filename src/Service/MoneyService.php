<?php

namespace App\Service;

use App\Dto\AccountCreateDto;
use App\Dto\AccountCreateResultDto;
use App\Dto\AccountMoneyCreateResultDto;
use App\Dto\AccountMoneyLoginResultDto;
use App\Dto\InitMoneyResultDto;
use App\Dto\PayMoneyDto;
use App\Dto\PayMoneyResultDto;
use App\Dto\PayTokenDto;
use App\Dto\TokenCreateDto;
use App\Dto\TokenDto;

interface MoneyService
{
  public const WSO2_AUTHORIZATION  = 'WSO2-Authorization';
  public const X_AUTH_TOKEN = 'X-AUTH-TOKEN';
  public const AUTHORIZATION = 'authorization';

  public const BASIC = 'Basic ';
  public const BEARER = 'Bearer ';

  public function init(?string $key = null) :InitMoneyResultDto;
  public function pay(PayMoneyDto $payMoneyDto, ?string $key = null):PayMoneyResultDto;

  public function getStatus(string $key ,?string $payToken = null): PayMoneyResultDto;

  public function generatePayToken(?string $key) : PayTokenDto;

  public function generateToken(TokenCreateDto $tokenCreateDto) : TokenDto;

  public function checkCredentials() : void;

  public function createMoneyAccount(AccountCreateDto $createDto) : AccountMoneyCreateResultDto;

  public function loginMoneyAccount(AccountCreateDto $createDto) : AccountMoneyLoginResultDto;

  public function regenerateKeyAccount(AccountCreateDto $createDto) : AccountMoneyCreateResultDto;
}