<?php

namespace App\Service;

use App\Dto\MoneyCallbackDto;

interface HttpService
{
    public function callBack(MoneyCallbackDto $moneyCallbackDto, string $url);
}