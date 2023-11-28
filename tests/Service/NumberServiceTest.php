<?php

namespace App\Tests\Service;

use App\Service\NumberService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NumberServiceTest extends KernelTestCase
{
    public function getNumberService()
    {
        self::bootKernel();
        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();
        // (3) run some service & test the result
        $numberService = $container->get(NumberService::class);
        return $numberService;
    }

    public function testGenerateBill(){
        $numberService = $this->getNumberService();
        $number =  $numberService->generateNumber();
        dd($number);
    }


}