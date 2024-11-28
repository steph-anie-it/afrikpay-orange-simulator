<?php

namespace App\Controller;

use App\Command;
use App\Dto\AccountCreateDto;
use App\Dto\AccountCreateResultDto;
use App\Dto\AccountMoneyCreateDto;
use App\Dto\CommandMessage;
use App\Dto\InitMoneyResultDto;
use App\Dto\PayAirtimeDto;
use App\Dto\PayAirtimeResultDto;
use App\Dto\PayMoneyDto;
use App\Dto\PayMoneyResultDto;
use App\Dto\TokenCreateDto;
use App\Response\AccountAirtimeResponse;
use App\Response\AccountMoneyResponse;
use App\Response\GenerateAirtimeResponse;
use App\Response\MoneyInitResponse;
use App\Response\MoneyPayResponse;
use App\Response\PayAirtimeResponse;
use App\Response\TokenResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

interface MoneyController
{
    public const MONEY_TOKEN_URI = 'token';
    public const MONEY_TOKEN_NAME = 'token';
    public const MONEY_ACCOUNT_CASHOUT_CREATE_URI = '/money/account/cashout/create';
    public const MONEY_ACCOUNT_CASHIN_CREATE_URI = '/money/account/cashin/create';
    public const MONEY_ACCOUNT_MP_CREATE_URI = '/money/account/mp/create';
    public const MONEY_ACCOUNT_MP_CREATE_NAME = 'money_account_mp_name';
    public const MONEY_ACCOUNT_CASHIN_CREATE_NAME = 'money_account_cashin_name';
    public const MONEY_ACCOUNT_CASHOUT_CREATE_NAME = 'money_account_cashout_name';
    public const CASHOUT = 'cashout';
    public const CASHIN = 'cashin';
    public const MP = 'mp';
    public const INIT_URI = '/init';


    public const CASHIN_INIT = '/cashin/init';

    public const CASHIN_PAY = '/cashin/pay';
    public const CASHIN_INIT_NAME = 'cashin_init_name';

    public const CASHIN_PAY_NAME = 'cashin_pay_name';
    public const MP_INIT = '/mp/init';
    public const MP_INIT_NAME = 'mp_init_name';

    public const CASHOUT_INIT = '/cashout/init';
    public const CASHOUT_INIT_NAME = 'cashout_init_name';

    public const CASHOUT_PAY = '/cashout/pay';
    public const CASHOUT_PAY_NAME = 'cashout_pay_name';
    public const MP_PAY = '/mp/pay';

    public const MP_PAY_NAME = 'mp_pay_name';


    public const POST_METHOD = 'POST';
    public const GET_METHOD = 'GET';

    public const  X_AUTH_TOKEN = 'X-AUTH-TOKEN';
    public const  WSO2_Authorization = 'WSO2-Authorization';

    public function initCashout(): MoneyInitResponse;
    public function initCashin(): MoneyInitResponse;
    public function initMp(): MoneyInitResponse;
    public function payMoneyCashout(PayMoneyDto $payMoneyDto): MoneyPayResponse;
    public function payMoneyCashin(PayMoneyDto $payMoneyDto): MoneyPayResponse;
    public function payMoneyMp(PayMoneyDto $payMoneyDto): MoneyPayResponse;

    public function createMpAccount(AccountMoneyCreateDto $accountMoneyCreateDto): AccountMoneyResponse;
    public function createCashoutAccount(AccountMoneyCreateDto $accountMoneyCreateDto): AccountMoneyResponse;

    public function createCashinAccount(AccountMoneyCreateDto $accountMoneyCreateDto): AccountMoneyResponse;

    public function generateToken(TokenCreateDto $tokenCreateDto): TokenResponse;


}