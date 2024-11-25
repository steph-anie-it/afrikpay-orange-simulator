<?php

namespace App\Service\impl;

use App\Controller\MoneyController;
use App\Dto\AccountCreateDto;
use App\Dto\AccountCreateResultDto;
use App\Dto\AccountMoneyCreateResultDto;
use App\Dto\InitMoneyResultDto;
use App\Dto\PayMoneyDto;
use App\Dto\PayMoneyResultDto;
use App\Dto\PayTokenDto;
use App\Exception\InvalidCredentialsException;
use App\Exception\InvalidMoneyCredentialsException;
use App\Repository\AccountRepository;
use App\Service\MoneyService;
use App\Service\NumberService;
use App\Service\UtilService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MoneyServiceImpl implements MoneyService
{
    public function __construct(
        public RequestStack $requestStack,
        public NumberService $numberService,
        protected AccountRepository $accountRepository,
        protected UserPasswordHasherInterface  $passwordHasher,
        public UtilService $utilService)
    {

    }

    public function checkCredentials() : void
    {
        $request = $this->requestStack->getCurrentRequest();
        $xAuthToken =  $request->headers->get(self::X_AUTH_TOKEN);
        if (!$xAuthToken){
            throw new InvalidCredentialsException();
        }
        $value = base64_decode($xAuthToken);
        $cred =  explode(':',$value);
        if (sizeof($cred) != 2){
            throw new InvalidMoneyCredentialsException();
        }

        $username = $cred[0];

        $account = $this->accountRepository->findOneBy(['username' => $username]);
        if (!$account){
            throw new InvalidMoneyCredentialsException();
        }
        $password = $cred[1];

        if (!$this->passwordHasher->isPasswordValid($account,$password)){
            throw new InvalidMoneyCredentialsException();
        }

        dd($account);

        $wsoAutorization = $request->headers->get(self::WSO2_AUTHORIZATION);

        if (!$wsoAutorization){
            dd($wsoAutorization);
        }

        dd($xAuthToken,$wsoAutorization);
    }

    public function init(?string $key = null): InitMoneyResultDto
    {
        $this->checkCredentials();
        return new InitMoneyResultDto(
           data: $this->generatePayToken($key)
        );
    }

    public function generatePayToken(?string $key) : PayTokenDto{
        $payTokenPrefix = "";
        switch ($key){
            case MoneyController::CASHOUT_INIT:
                $payTokenPrefix = 'CO';
                break;
            case MoneyController::CASHIN_INIT:
                $payTokenPrefix = 'CI';
                break;
            case MoneyController::MP_INIT:
                $payTokenPrefix =  'MP';
                break;
        }
        $payToken = strtoupper(sprintf("%s%s.%s.%s%s",$payTokenPrefix,
            $this->utilService->generateRandomNumber(6),
            $this->utilService->generateRandomNumber(4),
            $this->utilService->generateRandomString(1),
            $this->utilService->generateRandomNumber(5)
        ));

        return new PayTokenDto(
            $payToken
        );
    }

    public function pay(PayMoneyDto $payMoneyDto,?string $key = null): PayMoneyResultDto
    {
        $this->checkCredentials();
        // TODO: Implement pay() method.
    }

    public function createMoneyAccount(AccountCreateDto $createDto): AccountMoneyCreateResultDto
    {
           $result = $this->numberService->createAirtimeAccount($createDto);
           return $this->utilService->map($result,AccountMoneyCreateResultDto::class);
    }

}