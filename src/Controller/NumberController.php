<?php

namespace App\Controller;

use App\Command;
use App\Dto\AccountCreateDto;
use App\Dto\AccountCreateResultDto;
use App\Dto\PayAirtimeDto;
use App\Dto\PayAirtimeResultDto;
use App\Response\AccountAirtimeResponse;
use App\Response\GenerateAirtimeResponse;
use App\Response\PayAirtimeResponse;
use Symfony\Component\HttpFoundation\Request;

interface NumberController
{
    public const BASE_NAME = 'number';
    public const GENERATE_URI = '/api/number/generate';
    public const GENERATE_NAME = 'number_generate_get';
    public const GENERATE_DESCRIPTION = 'Generate a number';
    public const GENERATE_METHOD = 'GET';

    public const PAY_AIRTIME_URI = '/api/airtime/pay';
    public const PAY_AIRTIME_NAME = 'number_pay_airtime_post';
    public const PAY_AIRTIME_DESCRIPTION = 'Pay airtime for a number';
    public const PAY_AIRTIME_METHOD = 'POST';


    public const ACCOUNT_AIRTIME_URI = '/api/airtime/account/create';
    public const ACCOUNT_AIRTIME_NAME = 'number_account_airtime_post';
    public const ACCOUNT_AIRTIME_DESCRIPTION = 'Create an airtime account';
    public const ACCOUNT_AIRTIME_METHOD = 'POST';

    public function createAccount(AccountCreateDto $createDto): AccountAirtimeResponse;
    public function generateNumber(): GenerateAirtimeResponse;
    public function payAirtime(Request $request, \App\Dto\Command $payAirtimeDto):\App\Response\Command;
}