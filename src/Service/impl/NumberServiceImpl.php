<?php

namespace App\Service\impl;

use App\Dto\AccountCreateDto;
use App\Dto\AccountCreateResultDto;
use App\Dto\CommandHeaderDto;
use App\Dto\GenerateNumberDto;
use App\Dto\GenerateNumberResultDto;
use App\Dto\PayAirtimeDto;
use App\Dto\PayAirtimeFullDto;
use App\Dto\PayAirtimeResultDto;
use App\Dto\PayAirtimeTransactionResultDto;
use App\Dto\Result\CommandResultDto;
use App\Entity\Account;
use App\Entity\Number;
use App\Entity\Transaction;
use App\Exception\AccountNotFoundException;
use App\Exception\BadPinNumberException;
use App\Exception\InsufficientBalanceException;
use App\Exception\InvalidAccountPhoneNumberException;
use App\Exception\InvalidApiKeyException;
use App\Exception\InvalidCredentialsException;
use App\Exception\InvalidDataException;
use App\Exception\InvalidPhoneNumberException;
use App\Exception\NonUniqueAccountNameException;
use App\Exception\NonUniqueExternalIdException;
use App\Exception\ParameterNotFoundException;
use App\Exception\UnableToGeneratePhoneException;
use App\Exception\UnspecifedParameterException;
use App\Repository\AccountRepository;
use App\Repository\NumberRepository;
use App\Repository\TransactionRepository;
use App\Service\NumberService;
use App\Service\UtilService;
use http\Exception\InvalidArgumentException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class NumberServiceImpl implements NumberService
{

    public const BADPARAMETER_FORMAT="%s,%s";
    public function __construct(protected NumberRepository $numberRepository,
                                protected AccountRepository $accountRepository,
                                protected  UtilService $utilService,
                                protected TransactionRepository $transactionRepository,
                                protected UserPasswordHasherInterface  $passwordHasher
    ){

    }


    public function check(PayAirtimeDto $payAirtimeDto){
        if(!isset($payAirtimeDto->api_key)){
            $api_key = "api_key";
            throw new UnspecifedParameterException($api_key);
        }

        if(!isset($payAirtimeDto->msisdn)){
            $msisdn = "msisdn";
            throw new UnspecifedParameterException($msisdn);
        }

        if(!isset($payAirtimeDto->amount)){
            $amount ="amount";
            throw new UnspecifedParameterException($amount);
        }

        if(!isset($payAirtimeDto->ext_trans_id)){
            $ext_trans_id="ext_trans_id";
            throw new UnspecifedParameterException($ext_trans_id);
        }


        if(isset($payAirtimeDto->msisdn)){
            if(!preg_match($_ENV['PHONE_REGEX'],$payAirtimeDto->msisdn)){
                throw new InvalidPhoneNumberException($payAirtimeDto->msisdn);
            }
        }
    }

    /**
     * @throws InsufficientBalanceException
     * @throws NonUniqueExternalIdException
     * @throws AccountNotFoundException
     * @throws \ReflectionException
     */
    public function payAirtime(PayAirtimeFullDto $payAirtimeFullDto): CommandResultDto
    {
        $payAirtimeDto = $payAirtimeFullDto->command;
        $header = $payAirtimeFullDto->commandHeaderDto;

        $transaction = $this->utilService->map($payAirtimeDto,Transaction::class);

        $this->checkAccount($header,$transaction);

        $transactionId = null;

        $trans = $this->transactionRepository->findOneBy([Transaction::EXTREFNUM => $payAirtimeFullDto->command->EXTREFNUM]);

        if ($trans){
            throw new NonUniqueExternalIdException($payAirtimeDto->EXTREFNUM,$trans);
        }

        if($transaction instanceof Transaction){
            $transactionId = $this->utilService->generateTransactionId();
            $transaction->setTransactionid($transactionId);
            $date = new \DateTime();
            $transaction->setDateTransaction($date);
            $transaction->setDateEndTransaction($date);
            $transaction->setTxnStatus(strval(100));
            $transaction->setTxnid($this->utilService->generateTransactionId());
        }
        $transaction = $this->transactionRepository->save($transaction);
        $this->checkAccount($header,$transaction);

        $number = $this->numberRepository->findOneBy([Number::MSISDN=> $transaction->getMsisdn2()]);
        if(!$number){
            throw new InvalidPhoneNumberException($transaction->getMsisdn2(),$transaction);
        }

        $account = null;
        if($account instanceof Account){
            $balance = $account->getBalance();
            $oldBalance  = $account->getNewbalance() ?? $balance;
            $cBalance = $oldBalance - floatval($payAirtimeDto->AMOUNT);
            if($cBalance < 0){
                throw new InsufficientBalanceException($account->getMsisdn(),$transaction);
            }
        }

        $transaction = $this->transactionRepository->findOneBy([Transaction::TRANSACTION_ID => $transactionId]);

        $result = null;
        if($transaction){
           $transaction->setDateEndTransaction(new \DateTime());
           $transaction->setTxnStatus(strval(200));
           $transaction->setDate($transaction->getDateTransaction()->format('Y:m:d H:i:s'));
           $transaction = $this->transactionRepository->save($transaction);
           $result = $this->utilService->map($transaction,CommandResultDto::class,true);
        }

        return $result;
    }

    private function checkAccount(CommandHeaderDto $header,Transaction $transaction=null){
        $mappedAccount = $this->utilService->mapWithUnder($header,Account::class);
        if (!($mappedAccount instanceof Account)) {
            throw new InvalidDataException();
        }

        $account = $this->accountRepository->findOneBy([
            Account::LOGIN => $mappedAccount->getLogin()]
        );

        if(!$account){
            throw new AccountNotFoundException($mappedAccount->getLogin(),$transaction);
        }

        $account = $this->accountRepository->findOneBy([
                Account::REQUESTGATEWAYCODE => $mappedAccount->getRequestgatewaycode()]
        );

        if(!$account){
            $message = sprintf(self::BADPARAMETER_FORMAT,Account::REQUESTGATEWAYCODE,$mappedAccount->getRequestgatewaycode());
            throw new ParameterNotFoundException($message,$transaction);
        }

        if(!$this->passwordHasher->isPasswordValid($account,$mappedAccount->getPassword())){
            throw new InvalidCredentialsException($account->getLogin(),$transaction);
        }

        $account = $this->accountRepository->findOneBy([
                Account::REQUESTGATEWAYTYPE => $mappedAccount->getRequestgatewaytype()]
        );

        if(!$account){
            $message = sprintf(self::BADPARAMETER_FORMAT,Account::REQUESTGATEWAYTYPE,$mappedAccount->getRequestgatewaytype());
            throw new ParameterNotFoundException($message,$transaction);
        }

        $account = $this->accountRepository->findOneBy([
                Account::SERVICEPORT => $mappedAccount->getServiceport()]
        );

        if(!$account){
            $message = sprintf(self::BADPARAMETER_FORMAT,Account::SERVICEPORT,$mappedAccount->getServiceport());
            throw new ParameterNotFoundException($message,$transaction);
        }

        $account = $this->accountRepository->findOneBy([
                Account::SOURCETYPE => $mappedAccount->getSourcetype()]
        );

        if(!$account){
            $message = sprintf(self::BADPARAMETER_FORMAT,Account::SOURCETYPE,$mappedAccount->getSourcetype());
            throw new ParameterNotFoundException($message,$transaction);
        }

        if($transaction &&  $account->getPin() != $transaction->getPin()){
            throw new BadPinNumberException("",$transaction);
        }

        return $account;
    }

    public function generateNumber(): GenerateNumberResultDto
    {
        $phone =  $this->utilService->generatePhone();
        while ($this->accountRepository->findOneBy([Account::ACCOUNT_MSISDN => $phone])){
            $phone = $this->utilService->generatePhone();
        }

        $name = $this->utilService->generateCustomerName();
        $balance = $this->utilService->generateBalance();
        $generateNumberDto = new GenerateNumberDto();
        $generateNumberDto->customername = $name;
        $generateNumberDto->numbernewbalance = $balance;
        $generateNumberDto->numberphone = $phone;

        $number = $this->map($generateNumberDto,Number::class);

        if($number instanceof Number){
            $number->setNumberid($this->getUniqueid());
            $number->setAccountNumber($this->utilService->generateAccountNumber());
            $number->setMsisdn($phone);
        }
        $number = $this->numberRepository->save($number);
        $result = $this->utilService->map($number,GenerateNumberResultDto::class);
        return $result;
    }

    public function createAirtimeAccount(AccountCreateDto $createDto): AccountCreateResultDto
    {
        $account  = $this->accountRepository->findOneBy([Account::USERNAME => $createDto->username]);

        if($account){
            throw new NonUniqueAccountNameException($createDto->username);
        }
        $account = $this->buildAccount($createDto);
        $clearApiKey = $this->utilService->generateRandom();
        $hashApiKey = hash("sha256",$clearApiKey);
        $account->setApikey($hashApiKey);
        $account =   $this->accountRepository->save($account);
        $result = $this->map($account,AccountCreateResultDto::class);
        if($result instanceof AccountCreateResultDto){
            $result->apikey = $clearApiKey;
        }
        return  $result;
    }

    public function buildAccount(AccountCreateDto $createDto):Account{
        $account =  $this->map($createDto,Account::class);
        if($account instanceof Account){
            $phone1 = $this->generatePhone();
            $phone2 = $this->generatePhone();

            $account->setAccountId($this->getUniqueid());
            $account->setMsisdn($phone1);
//            $account->setMsisdn2($phone2);
            $account->setType($this->utilService->generateRandomNumber($_ENV['TYPE_LENGTH']));
            $account->setSelector($this->utilService->generateRandomNumber($_ENV['SELECTOR_LENGTH']));
            $account->setExtnwcode(strtoupper($this->utilService->generateRandomString($_ENV['EXTNWCODELENGTH'])));
            $account->setPin($this->utilService->generateRandomNumber($_ENV['PIN_LENGTH']));
            $account->setLanguage2($this->utilService->generateRandomNumber($_ENV['LANGUAGE_LENGTH']));
            $account->setLanguage1($this->utilService->generateRandomNumber($_ENV['LANGUAGE_LENGTH']));
            $account->setAccountNumber($this->utilService->generateAccountNumber(true));
            $account->setRequestgatewaytype(strtoupper($this->utilService->generateRandomString($_ENV['REQUEST_GATEWAY_TYPE_LENGTH'])));
            $account->setType(strtoupper($this->utilService->generateRandomString($_ENV['TYPE_LENGTH'])));
            $account->setBalance($this->utilService->generateBalance());
            $account->setLogin($account->getUsername());
            $account->setSourcetype(strtoupper($this->utilService->generateRandomString($_ENV['SOURCE_TYPE_LENGTH'])));
            $account->setServiceport($this->utilService->generateRandomRangeNumber($_ENV['SERVICE_PORT_MIN'],$_ENV['SERVICE_PORT_MAX']));
            $account->setRequestgatewaycode(strtoupper($this->utilService->generateRandomString($_ENV['REQUEST_GATEWAY_CODE_LENGTH'])));

            $hashedPassword = $this->passwordHasher->hashPassword(
                $account,
                $createDto->password
            );

            $account->setPassword($hashedPassword);

        }
        return  $account;
    }

    public function generatePhone(){
        $phone = $this->utilService->generatePhone();
        $atempt= 0;
        while ($this->accountRepository->findOneBy([Account::ACCOUNT_MSISDN => $phone])){
            $phone = $this->utilService->generatePhone();
            $atempt++;
            if($atempt > $_ENV['MAX_GENERATION_ATTEMPT']){
                throw new UnableToGeneratePhoneException($atempt);
            }
        }
        return $phone;
    }

    public function getUniqueid() : int{
        return  $this->utilService->generateUnique();
    }

    public function map(mixed $sourceObject , string $destinationClass) : mixed{
        return $this->utilService->map($sourceObject,$destinationClass);
    }

    public function payeAirtime(string $xmlString): mixed
    {
        dd($xmlString);
        // TODO: Implement payeAirtime() method.
    }
}