<?php

namespace App\Service;

use App\Dto\NumberResultDto;

interface NumberService
{
    public function generateNumber():NumberResultDto;
}