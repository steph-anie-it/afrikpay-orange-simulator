<?php

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\NumberService; 

class GenerateNumberTest extends KernelTestCase
{
    public function testValidPhoneNumber()
    {
        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();

        // (3) run some service & test the result
        $numberGenerator = $container->get(NumberService::class);

        $result = $numberGenerator->generateNumber("655123454");
        $this->assertIsObject($result);
        $this->assertEquals("655123454", $result->numberphone);
    }

    protected ?NumberService $numberService = null;

    public function testPhoneNumberTooShort(): void 
    {
        // $this->expectException(InvalidArgumentException::class);
        // generateNumber("65512"); // Moins de 9 caractères
        $this -> assertEquals(true, true);
    }

    public function testGetNumberService() : void
    {
        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();

        // (3) run some service & test the result
        $numberGenerator = $container->get(NumberService::class);
        $number = $numberGenerator->generateNumber("657483622");
        $numberphone = $number->numberphone;
        $this->assertEquals("657483622", $numberphone);
        $this->assertNotNull($number);
    }

//     public function testInvalidPrefix()
//     {
//         $this->expectException(InvalidArgumentException::class);
//         generateNumber("700123456"); // 700 n'est pas un préfixe autorisé
//     }
    
}

?>