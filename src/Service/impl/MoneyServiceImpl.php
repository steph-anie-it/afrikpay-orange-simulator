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
        //$this->checkCredentials();
        $payTokenData = $this->generatePayToken($key);
        $transaction = new Transaction();
        $transaction->setMoneytype($key);
        $transaction->setTransactionid($this->utilService->generateTransactionId());
        $transaction->setPaytoken($payTokenData->payToken);
        $transaction->setStatus(Transaction::PENDING);
        $this->transactionRepository->save($transaction);
        return new InitMoneyResultDto(
           data: $payTokenData
        );
    }

    public const PAY_TOKEN_TEMPLATE = '%s%s%s%s%s';
    public function generatePayToken(?string $key) : PayTokenDto{
        $payTokenPrefix = "";
        switch ($key){
            case MoneyController::CASHOUT:
                $payTokenPrefix = 'CO';
                break;
            case MoneyController::CASHIN:
                $payTokenPrefix = 'CI';
                break;
            case MoneyController::MP:
                $payTokenPrefix =  'MP';
                break;
        }
        $payToken = strtoupper(sprintf(self::PAY_TOKEN_TEMPLATE,$payTokenPrefix,
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

       // $multiple = floatval($_ENV['AMOUNT_MONEY_MUTIPLE']);
      //  $this->checkMultiple($amount,$multiple);
    }

    public function checkMultiple(float $amount, float $multiple):void
    {
        if(fmod($amount , $multiple) != floatval(0)){
            $message = sprintf(self::BADPARAMETER_FORMAT,strval($amount),strval($multiple));
            throw new GeneralException($message,null,ResponseStatus::BAD_AMOUNT_MULTIPLE);
        }
    }

    public const PAIEMENT_MESSAGE_TEMPLATE = '%s %s %s from %s to %s  Paiment %s done successfully';

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

        $number = $this->numberRepository->findOneBy([Number::MSISDN => $msisdn]);
        if (!$number){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::UNKNOWN_MONEY_NUMBER,
                payMoneyDataResultDto: $payMoneyResultDto
            );
        }

        if (!$number->getIsMoney()){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_MONEY_NUMBER,
                payMoneyDataResultDto: $payMoneyResultDto
            );
        }

        $transaction = $this->transactionRepository->findOneBy(
            ['paytoken' => $payMoneyDto->payToken,
             'moneytype' => $key
            ]);

        if (!$transaction){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_PAY_TOKEN_NUMBER,
                payMoneyDataResultDto: $payMoneyResultDto
            );
        }


        if ($transaction->getStatus() != Transaction::PENDING){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_PAY_TOKEN_TRANSACTION_NUMBER,
                payMoneyDataResultDto: $payMoneyResultDto
            );
        }

        try{
            $this->checkAmount($payMoneyDto->amount);
        }catch (GeneralException $generalException){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_AMOUNT,
                payMoneyDataResultDto: $payMoneyResultDto
            );
        }

        $amount = $payMoneyDto->amount + $this->getFees($account);

        $balance =  $account->getBalance();
        if ($transaction->getMoneytype() == MoneyController::CASHOUT)
        {
            if ($amount > $balance){
                throw new MoneyPayException(
                    exceptionValues: ExceptionList::NOT_ENOUGH_FUND,
                    payMoneyDataResultDto: $payMoneyResultDto
                );
            }
            $newBalance = $balance - $amount ;
            $account->setOldbalance($balance);
            $account->setNewbalance($newBalance);
            $account->setBalance($newBalance);
            $this->accountRepository->save($account);
        }

        $transaction->setAmount($amount);
        $transaction->setStatus(Transaction::SUCCESS);
        $inittxnstatus = 'inittxnstatus';
        $inittxnmessage = 'inittxnmessage';
        $txnid = 'txnid';
        $confirmtxnmessage = 'confirmtxnmessage';
        $confirmtxnstatus = 'confirmtxnstatus';
        $txnmode = 'txnmode';
        $txnidValue = $this->utilService->generateTransactionId();
        $transaction->setTxnid($txnidValue);
        $transaction->setBalance($account->getBalance());
        $transaction->setPin($account->getPin());
        
        $this->transactionRepository->save($transaction);
        $payMoneyResultDto->status = $transaction->getStatus();
        $payMoneyResultDto->$inittxnstatus = "200";
        $payMoneyResultDto->$txnid = $transaction->getTxnid();
        $payMoneyResultDto->$confirmtxnmessage = 'Paiement success';
        $payMoneyResultDto->$inittxnmessage = 'Paiement success';
        $payMoneyResultDto->$confirmtxnstatus = "200";
        $payMoneyResultDto->$txnmode = 'SUCCESS';
        return new PayMoneyResultDto(
            $payMoneyResultDto,
            sprintf(self::PAIEMENT_MESSAGE_TEMPLATE,
                $key,
                $txnidValue,
                $payMoneyDto->amount,
                $account->getMsisdn(),
                $number->getMsisdn(),
                $payMoneyDto->payToken
            )
        );
    }

    public function getFees(Account $account) : float
    {
        return 0;
    }

    public function createMoneyAccount(AccountCreateDto $createDto): AccountMoneyCreateResultDto
    {
           $result = $this->numberService->createAirtimeAccount($createDto);
           return $this->utilService->map($result,AccountMoneyCreateResultDto::class);
    }

}