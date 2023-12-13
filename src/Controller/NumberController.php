<?php

namespace App\Controller;

use App\Command;
use App\Dto\AccountCreateDto;
use App\Dto\AccountCreateResultDto;
use App\Dto\CommandMessage;
use App\Dto\PayAirtimeDto;
use App\Dto\PayAirtimeResultDto;
use App\Response\AccountAirtimeResponse;
use App\Response\GenerateAirtimeResponse;
use App\Response\PayAirtimeResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    public const PAY_NUMBER_AIRTIME_URI = '/api/number/airtime/pay';
    public const PAY_NUMBER_AIRTIME_NAME = 'number_pay_airtime_post';
    public const PAY_NUMBER_AIRTIME_DESCRIPTION = 'Pay airtime for a number';
    public const PAY_NUMBER_AIRTIME_METHOD = 'POST';


    public const ACCOUNT_AIRTIME_URI = '/api/airtime/account/create';
    public const ACCOUNT_AIRTIME_NAME = 'number_account_airtime_post';
    public const ACCOUNT_AIRTIME_DESCRIPTION = 'Create an airtime account';
    public const ACCOUNT_AIRTIME_METHOD = 'POST';


    public const TRANSACTION_STATUS_URI = '/api/transaction/status';
    public const TRANSACTION_STATUS_NAME = 'transaction_status_post';
    public const TRANSACTION_STATUS_DESCRIPTION = 'Status of a transaction';
    public const TRANSACTION_STATUS_METHOD = 'POST';


    public const ACCOUNT_LIST_URI = '/api/account/list';
    public const ACCOUNT_LIST_NAME = 'account_list_post';
    public const ACCOUNT_LIST_DESCRIPTION = 'List account';
    public const ACCOUNT_LIST_METHOD = 'POST';


    public const PAY_DATA_URI = '/api/data/pay';
    public const PAY_DATA_NAME = 'number_pay_data_post';
    public const PAY_DATA_DESCRIPTION = 'Pay data for a number';
    public const PAY_DATA_METHOD = 'POST';


    public const API_BALANCE_URI = '/api/balance';
    public const API_BALANCE_NAME = 'balance_post';
    public const API_BALANCE_DESCRIPTION = 'Get the balance of the account';
    public const API_BALANCE_METHOD = 'POST';


    public const API_MESSAGE_NEW_URI = '/api/message/new';
    public const API_MESSAGE_NEW_NAME = 'new_message_post';
    public const API_MESSAGE_NEW_DESCRIPTION = 'Create new message';
    public const API_MESSAGE_NEW_METHOD = 'POST';


    public const ACCOUNT_AIRTIME_LOGIN_URI = '/api/airtime/account/login';
    public const ACCOUNT_AIRTIME_LOGIN_NAME = 'log_account_airtime_post';
    public const ACCOUNT_AIRTIME_LOGIN_DESCRIPTION = 'Login to the airtime account';
    public const ACCOUNT_AIRTIME_LOGIN_METHOD = 'POST';


    public function createAccount(AccountCreateDto $createDto): AccountAirtimeResponse;
    public function generateNumber(): GenerateAirtimeResponse;
    public function payAirtime(Request $request, \App\Dto\Command $payAirtimeDto):\App\Response\Command;

    public function payNumberAirtime(Request $request):\App\Response\Command;

    public function payData(Request $request):\App\Response\Command;


    public function listAccount():Response;

    public function transactionStatus(Request $request, \App\Dto\Command $payAirtimeDto):\App\Response\Command;

    public function checkBalance(Request $request):\App\Response\Command;

    public function newMessage(CommandMessage $commandMessage):\App\Response\Command;

    public function loginAccount(AccountCreateDto $createDto): AccountAirtimeResponse;
}