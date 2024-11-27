<?php

namespace App\Service\impl;

use App\Controller\MoneyController;
use App\Dto\AccountCreateDto;
use App\Dto\AccountCreateResultDto;
use App\Dto\AccountMoneyCreateResultDto;
use App\Dto\InitMoneyResultDto;
use App\Dto\PayMoneyDataResultDto;
use App\Dto\PayMoneyDto;
use App\Dto\PayMoneyResultDto;
use App\Dto\PayTokenDto;
use App\Entity\Account;
use App\Entity\Number;
use App\Entity\Transaction;
use App\Exception\ExceptionList;
use App\Exception\GeneralException;
use App\Exception\InvalidCredentialsException;
use App\Exception\InvalidMoneyCredentialsException;
use App\Exception\MoneyPayException;
use App\Model\ResponseStatus;
use App\Repository\AccountRepository;
use App\Repository\NumberRepository;
use App\Repository\TransactionRepository;
use App\Service\MoneyService;
use App\Service\NumberService;
use App\Service\UtilService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MoneyServiceImpl implements MoneyService
{
    public const BADPARAMETER_FORMAT="%s,%s";

    public const BADTRHREEPARAMETER_FORMAT="%s,%s,%s";
    public function __construct(
        public RequestStack $requestStack,
        public NumberService $numberService,
        protected AccountRepository $accountRepository,
        protected NumberRepository $numberRepository,
        protected UserPasswordHasherInterface  $passwordHasher,
        protected TransactionRepository $transactionRepository,
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
        $payTokenData = $this->generatePayToken($key);
        $transaction = new Transaction();
        $transaction->setTransactionid($this->utilService->generateTransactionId());
        $transaction->setPaytoken($payTokenData->payToken);
        $this->transactionRepository->save($transaction);
        return new InitMoneyResultDto(
           data: $payTokenData
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

    public function checkAmount(float $amount, ?Transaction $transaction = null): void
    {
        $minAmount = floatval($_ENV['MIN_MONEY_TRANSACTION_AMOUNT']);

        $maxAmount = floatval($_ENV['MAX_MONEY_TRANSACTION_AMOUNT']);

        if($amount < $minAmount){
            $message = sprintf(self::BADTRHREEPARAMETER_FORMAT,strval($amount),strval($minAmount),strval($maxAmount));
            throw new GeneralException($message,$transaction,ResponseStatus::INVALID_AMOUNT_MIN_MAX);
        }


        if($amount > $maxAmount){
            $message = sprintf(self::BADTRHREEPARAMETER_FORMAT,strval($amount),strval($minAmount),strval($maxAmount));
            throw new GeneralException($message,$transaction,ResponseStatus::INVALID_AMOUNT_MIN_MAX);
        }

        $multiple = floatval($_ENV['AMOUNT_MONEY_MUTIPLE']);
        $this->checkMultiple($amount,$multiple);
    }

    public function checkMultiple(float $amount, float $multiple):void
    {
        if(fmod($amount , $multiple) != floatval(0)){
            $message = sprintf(self::BADPARAMETER_FORMAT,strval($amount),strval($multiple));
            throw new GeneralException($message,null,ResponseStatus::BAD_AMOUNT_MULTIPLE);
        }
    }

    public function pay(PayMoneyDto $payMoneyDto,?string $key = null): PayMoneyResultDto
    {
        $payMoneyResultDto = $this->utilService->map($payMoneyDto,PayMoneyDataResultDto::class);

        $payMoneyResultDto->createtime = time();
        $orderId = $payMoneyDto->orderId;

        if (!$orderId){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_ORDER_ID,
                payMoneyDataResultDto: $payMoneyResultDto
            );
        }

        $channel = $payMoneyResultDto->channelUserMsisdn;

        if (!$channel){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_CHANNEL_NUMBER,
                payMoneyDataResultDto: $payMoneyResultDto
            );
        }

        $account = $this->accountRepository->findOneBy([Account::ACCOUNT_MSISDN => $channel]);

        if (!$account){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::ACCOUNT_NOT_FOUND,
                payMoneyDataResultDto: $payMoneyResultDto
            );
        }
        if ($account->getPin() != $payMoneyDto->pin){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_PIN_NUMBER,
                payMoneyDataResultDto: $payMoneyResultDto
            );
        }




        $msisdn = $payMoneyDto->subscriberMsisdn;
        if (!$msisdn){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_SUBSCRIBER_NUMBER,
                payMoneyDataResultDto: $payMoneyResultDto
            );
        }

        $user = $this->numberRepository->findOneBy([Number::MSISDN => $msisdn]);
        if (!$user){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::UNKNOWN_MONEY_NUMBER,
                payMoneyDataResultDto: $payMoneyResultDto
            );
        }

        $transaction = $this->transactionRepository->findOneBy(['payToken' => $payMoneyDto->payToken]);

        if (!$transaction){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_PAY_TOKEN_NUMBER,
                payMoneyDataResultDto: $payMoneyResultDto
            );
        }

        if ($transaction->getStatus() != Transaction::PENDING){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_PAY_TOKEN_NUMBER,
                payMoneyDataResultDto: $payMoneyResultDto
            );
        }

        //$this->checkCredentials();
        //$payToken = $payMoneyDto->payToken;
        $this->checkAmount($payMoneyDto->amount);
        $data = new PayMoneyDataResultDto();
        return new PayMoneyResultDto($data);
    }

    public function createMoneyAccount(AccountCreateDto $createDto): AccountMoneyCreateResultDto
    {
           $result = $this->numberService->createAirtimeAccount($createDto);
           return $this->utilService->map($result,AccountMoneyCreateResultDto::class);
    }

}