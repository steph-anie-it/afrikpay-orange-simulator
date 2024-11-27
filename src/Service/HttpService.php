<?php

namespace App\Service;

use App\Dto\PayMoneyDataResultDto;

interface HttpService
{
    public function callBack(PayMoneyDataResultDto $payMoneyDataResultDto);
}