<?php

namespace App\Service\impl;

use App\Dto\NumberGenerateDto;
use App\Dto\NumberResultDto;
use App\Entity\Number;
use App\Repository\NumberRepository;
use App\Service\NumberService;

class NumberServiceImpl implements NumberService
{

    public const  NUMBER_FORMAT="%s";
    public function __construct(protected NumberRepository $numberRepository){

    }

    public function generateNumber(): NumberResultDto
    {
        $regex= $_ENV['REFERENCE_REGEX'];
        $top = $_ENV['PHONE_TOP'];
        $bottom = $_ENV['PHONE_BOTTOM'];
        $random = rand($top,$bottom);
        $number = strval($random);

        while(!preg_match($regex,$number)){
            $number = rand($top,$bottom);
            $foundNumber = $this->numberRepository->findOneBy(['numberNumber'=>$number]);
            if($foundNumber) $number = "";
        }
        $numberEntity = new Number();
        $numberEntity->setNumberNumber($number);
        $numberEntity->setCustomerName(UserNameUtils::generateCustomerName());
        $numberEntity->setAirtimeBalance($_ENV['AIRTIME_INIT_BALANCE']);
        try{
          $numberEntity =   $this->numberRepository->save($numberEntity);
        }catch (\Throwable $throwable){

        }
        return  new NumberResultDto($numberEntity);
    }
}