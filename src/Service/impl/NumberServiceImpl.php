<?php

namespace App\Service\impl;

use App\Dto\AccountCreateDto;
use App\Dto\AccountCreateResultDto;
use App\Dto\Command;
use App\Dto\CommandHeaderDto;
use App\Dto\CommandMessage;
use App\Dto\ExecuteCommandDto;
use App\Dto\GenerateNumberDto;
use App\Dto\GenerateNumberResultDto;
use App\Dto\PayAirtimeDto;
use App\Dto\PayAirtimeFullDto;
use App\Dto\PayDataDto;
use App\Dto\PayDataFullDto;
use App\Dto\Result\BalanceResultDto;
use App\Dto\Result\CommandResultDto;
use App\Dto\Result\Record;
use App\Dto\TransactionStatusFullDto;
use App\Entity\Account;
use App\Entity\Message;
use App\Entity\Number;
use App\Entity\Transaction;
use App\Exception\AccountNotFoundException;
use App\Exception\BadPinNumberException;
use App\Exception\GeneralException;
use App\Exception\InsufficientBalanceException;
use App\Exception\InvalidCredentialsException;
use App\Exception\InvalidDataException;
use App\Exception\InvalidPhoneNumberException;
use App\Exception\NonUniqueAccountNameException;
use App\Exception\NonUniqueExternalIdException;
use App\Exception\ParameterNotFoundException;
use App\Exception\UnableToGeneratePhoneException;
use App\Exception\UnspecifedParameterException;
use App\Model\OperationNature;
use App\Model\ResponseStatus;
use App\Repository\AccountRepository;
use App\Repository\MessageRepository;
use App\Repository\NumberRepository;
use App\Repository\TransactionRepository;
use App\Service\NumberService;
use App\Service\UtilService;
use DateTime;
use http\Exception\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class NumberServiceImpl implements NumberService
{

    public const BADPARAMETER_FORMAT="%s,%s";

    public const BADTRHREEPARAMETER_FORMAT="%s,%s,%s";
    public function __construct(protected NumberRepository $numberRepository,
                                protected AccountRepository $accountRepository,
                                protected  UtilService $utilService,
                                protected TransactionRepository $transactionRepository,
                                protected UserPasswordHasherInterface  $passwordHasher,
                                protected MessageRepository $messageRepository
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

    public function buildTransaction(string $txnId, string $operation,?string $type=null, float $oldBalance = 0, float $newbalance=0) :Transaction
    {
        $transaction = new Transaction();
        $transactionId = $this->utilService->generateUnique();
        $transaction->setTransactionid($transactionId);
        $date = new \DateTime();
        $transaction->setDateTransaction($date);
        $transaction->setDateEndTransaction($date);
        $transaction->setTxnStatus(strval(200));
        $transaction->setOperation($operation);
        $transaction->setType($type);
        if($oldBalance){
            $transaction->setBalanceold($oldBalance);
        }
        if($newbalance){
            $transaction->setBalancenew($newbalance);
        }

        $transaction->setTxnid($txnId);
        return  $transaction;
    }


    /**
     * @throws GeneralException
     */
    private function checkMultiple(float $amount, float $multiple, Transaction $transaction):void
    {
        if(fmod($amount , $multiple) != floatval(0)){
            $message = sprintf(self::BADPARAMETER_FORMAT,strval($amount),strval($multiple));
            throw new GeneralException($message,$transaction,ResponseStatus::BAD_AMOUNT_MULTIPLE);
        }
    }

    /**
     * @throws GeneralException
     */
    public function checkAmount(float $amount, Transaction $transaction): void
    {
        $minAmount = floatval($_ENV['MIN_TRANSACTION_AMOUNT']);

        $maxAmount = floatval($_ENV['MAX_TRANSACTION_AMOUNT']);

        if($amount < $minAmount){
            $message = sprintf(self::BADTRHREEPARAMETER_FORMAT,strval($amount),strval($minAmount),strval($maxAmount));
            throw new GeneralException($message,$transaction,ResponseStatus::INVALID_AMOUNT_MIN_MAX);
        }


        if($amount > $maxAmount){
            $message = sprintf(self::BADTRHREEPARAMETER_FORMAT,strval($amount),strval($minAmount),strval($maxAmount));
            throw new GeneralException($message,$transaction,ResponseStatus::INVALID_AMOUNT_MIN_MAX);
        }

        $multiple = floatval($_ENV['AMOUNT_MUTIPLE']);
        $this->checkMultiple($amount,$multiple,$transaction);

    }


    /**
     * @param PayAirtimeFullDto $payAirtimeFullDto
     * @return CommandResultDto
     * @throws AccountNotFoundException
     * @throws BadPinNumberException
     * @throws InsufficientBalanceException
     * @throws InvalidCredentialsException
     * @throws InvalidDataException
     * @throws InvalidPhoneNumberException
     * @throws NonUniqueExternalIdException
     * @throws ParameterNotFoundException
     * @throws \ReflectionException
     * @throws GeneralException
     */
    public function payAirtime(PayAirtimeFullDto $payAirtimeFullDto): CommandResultDto
    {
        $payAirtimeDto = $payAirtimeFullDto->command;
        $header = $payAirtimeFullDto->commandHeaderDto;
        return $this->performAirtimeCommand($payAirtimeDto,$header);
    }


    public function checkOperation(string $operation,string $expected):void
    {
        if($operation != $expected){
            throw new GeneralException($operation,null,ResponseStatus::INVALID_TYPE_FOROPERATION);
        }
    }


    /**
     * @throws GeneralException
     */
    public function checkConnection(mixed $queryParams):void
    {
        $commandHeaderDto = $queryParams;
        if(is_array($queryParams)){
            $commandHeaderDto = $this->utilService->mapArray($queryParams,CommandHeaderDto::class);
        }

        if(!($commandHeaderDto instanceof CommandHeaderDto)){
          throw new GeneralException(null,null,ResponseStatus::INVALID_HEADER);
        }

        $undefinded = $this->utilService->getUndefinedParams($commandHeaderDto);
        if(!empty($undefinded)){
            $message = sprintf(self::BADPARAMETER_FORMAT,$undefinded,"");
            throw new GeneralException($message,null,ResponseStatus::INVALID_PARAMETER);
        }

//        $account = $this->accountRepository->findOneBy([Account::REQUESTGATEWAYTYPE => $commandHeaderDto->REQUEST_GATEWAY_TYPE]);
//        if(!$account){
//            $value = sprintf(self::BADPARAMETER_FORMAT,strtoupper(Account::REQUESTGATEWAYTYPE),$commandHeaderDto->REQUEST_GATEWAY_TYPE);
//            throw new GeneralException($value,null,ResponseStatus::INVALID_PARAMETER);
//        }

//        $account = $this->accountRepository->findOneBy([Account::REQUESTGATEWAYCODE => $commandHeaderDto->REQUEST_GATEWAY_CODE]);
//        if(!$account){
//            $value =  sprintf(self::BADPARAMETER_FORMAT, strtoupper(Account::REQUESTGATEWAYCODE),$commandHeaderDto->REQUEST_GATEWAY_CODE);
//            throw new GeneralException($value,null,ResponseStatus::INVALID_PARAMETER);
//        }

        $account = $this->accountRepository->findOneBy([Account::LOGIN => $commandHeaderDto->LOGIN]);
        if(!$account){
            $value = sprintf(self::BADPARAMETER_FORMAT,strtoupper(Account::LOGIN).",".$commandHeaderDto->LOGIN);
            throw new GeneralException($value,null,ResponseStatus::INVALID_PARAMETER);
        }

//        $account = $this->accountRepository->findOneBy([Account::SERVICEPORT => $commandHeaderDto->SERVICE_PORT]);
//        if(!$account){
//            $value = sprintf(self::BADPARAMETER_FORMAT,strtoupper(Account::SERVICEPORT),$commandHeaderDto->SERVICE_PORT);
//            throw new GeneralException($value,null,ResponseStatus::INVALID_PARAMETER);
//        }

//        $account = $this->accountRepository->findOneBy([Account::SOURCETYPE => $commandHeaderDto->SOURCE_TYPE]);
//        if(!$account){
//            $value = sprintf(strtoupper(Account::SOURCETYPE),$commandHeaderDto->SOURCE_TYPE);
//            throw new GeneralException($value,null,ResponseStatus::INVALID_PARAMETER);
//        }

        $account = $this->accountRepository->findOneBy([Account::LOGIN => $commandHeaderDto->LOGIN]);
        if(!$account){
            $value = sprintf(self::BADPARAMETER_FORMAT,strtoupper(Account::LOGIN),$commandHeaderDto->LOGIN);
            throw new GeneralException($value,null,ResponseStatus::INVALID_PARAMETER);
        }

        if(!isset($commandHeaderDto->PASSWORD)){
            $value = sprintf(self::BADPARAMETER_FORMAT,strtoupper(Account::PASSWORD),"");
            throw new GeneralException($value,null,ResponseStatus::INVALID_PARAMETER);
        }

        if(!$this->passwordHasher->isPasswordValid($account,$commandHeaderDto->PASSWORD)){
            throw new GeneralException($commandHeaderDto->LOGIN,null,ResponseStatus::INVALID_CREDENTIAL);
        }
    }


    /**
     * @throws GeneralException
     */
    public function checkDateFormat(string $date):void
    {
        if(!preg_match($_ENV['DATE_REGEX'],$date)){
            throw new GeneralException(null,null,ResponseStatus::INVALID_DATE_FORMAT);
        }
        if(!$this->isValidDate($date, $_ENV['DATE_FORMAT'])){
            throw new GeneralException(null,null,ResponseStatus::INVALID_DATE_VALUE);
        }
    }

    public function isValidDate($date, $format) :bool {
        $isValid = false;
        try{
            $dateTime = DateTime::createFromFormat($format, $date);
            $isValid = $dateTime && $dateTime->format($format) === $date;
        }catch (\Throwable $exception){

        }
        return $isValid;
    }

    public function checkCredentials(CommandHeaderDto $header, Transaction $transaction=null): Account
    {
        $mappedAccount = $this->utilService->mapWithUnder($header,Account::class);
        if (!($mappedAccount instanceof Account)) {
            throw new InvalidDataException();
        }

        $login = $header->LOGIN;

        $account = $this->accountRepository->findOneBy([
                Account::LOGIN => $login]
        );

        if(!$account){
            throw new GeneralException("",$transaction,ResponseStatus::ACCOUNT_NOT_FOUND);
        }

        if(!$this->passwordHasher->isPasswordValid($account,$mappedAccount->getPassword())){
            throw new GeneralException("",$transaction,ResponseStatus::INVALID_CREDENTIAL);
        }

        if($transaction &&  $account->getPin() != $transaction->getPin()){
            throw new GeneralException("",$transaction,ResponseStatus::BAD_PIN_NUMBER);
        }
        return $account;
    }

    public function checkAccount(CommandHeaderDto $header,Transaction $transaction=null): Account{
        $mappedAccount = $this->utilService->mapWithUnder($header,Account::class);
        if (!($mappedAccount instanceof Account)) {
            throw new InvalidDataException();
        }

        $login = $header->LOGIN ?? $header->LOGINID;

        $account = $this->accountRepository->findOneBy([
            Account::LOGIN => $login]
        );

        if(!$account){
            throw new AccountNotFoundException($mappedAccount->getLogin(),$transaction);
        }


//        $account = $this->accountRepository->findOneBy([
//                Account::REQUESTGATEWAYTYPE => $mappedAccount->getRequestgatewaytype()]
//        );

//        if(!$account){
//            $message = sprintf(self::BADPARAMETER_FORMAT,Account::REQUESTGATEWAYTYPE,$mappedAccount->getRequestgatewaytype());
//            throw new ParameterNotFoundException($message,$transaction);
//        }

//        $account = $this->accountRepository->findOneBy([
//                Account::SERVICEPORT => $mappedAccount->getServiceport()]
//        );
//
//        if(!$account){
//            $message = sprintf(self::BADPARAMETER_FORMAT,Account::SERVICEPORT,$mappedAccount->getServiceport());
//            throw new ParameterNotFoundException($message,$transaction);
//        }

//        $account = $this->accountRepository->findOneBy([
//                Account::SOURCETYPE => $mappedAccount->getSourcetype()]
//        );
//
//        if(!$account){
//            $message = sprintf(self::BADPARAMETER_FORMAT,Account::SOURCETYPE,$mappedAccount->getSourcetype());
//            throw new ParameterNotFoundException($message,$transaction);
//        }

        return $account;
    }

    /**
     * @param string|null $number
     * @param bool|null $isMoney
     * @return GenerateNumberResultDto
     * @throws GeneralException
     * @throws \ReflectionException
     */
    public function generateNumber(?string $number = null,?bool $isMoney = false): GenerateNumberResultDto
    {

        if($number){
            if(!preg_match($_ENV['PHONE_REGEX'],$number)){
                throw new GeneralException(null,null,ResponseStatus::INVALID_PHONE_NUMBER);
            }
          $accountExists =  $this->accountRepository->findOneBy([Account::ACCOUNT_MSISDN => $number]);
          if($accountExists){
              throw new GeneralException(null,null,ResponseStatus::NUMBER_ALREADY_EXISTS);
          }

          $numberExists =  $this->numberRepository->findOneBy([Number::MSISDN => $number]);
          if($numberExists && !$isMoney){
              throw new GeneralException(null,null,ResponseStatus::NUMBER_ALREADY_EXISTS);
          }
        }
        if (!isset($numberExists)){
            $phone = $number ?: $this->utilService->generatePhone();
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
        }else{
            $number = $numberExists;
        }
        $number->setIsMoney($isMoney);

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
        $hashApiKey =   password_hash($clearApiKey,PASSWORD_BCRYPT);
        $account->setApikey($hashApiKey);
        $subcriptionKey = $this->utilService->generateRandom(30);
        $hasSubKey = password_hash($subcriptionKey,PASSWORD_BCRYPT);
        $account->setSubscriptionkey($hasSubKey);
        $account =   $this->accountRepository->save($account);
        $result = $this->map($account,AccountCreateResultDto::class);
        if($result instanceof AccountCreateResultDto){
            $result->apikey = $clearApiKey;
            $result->subscriptionkey = $subcriptionKey;
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
            $account->setCurrency($_ENV['TRANSACTION_CURRENCY']);
            $account->setBalance($this->utilService->generateBalance());
            $account->setDatabalance($this->utilService->generateBalance());
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

    public function payData(PayDataFullDto $param): CommandResultDto
    {
        return new CommandResultDto();
    }


    /**
     * @throws \ReflectionException
     * @throws InvalidDataException
     * @throws GeneralException
     */
    public function payInternetData(PayDataDto $payDataDto): CommandResultDto
    {
        $payAirtimeDto = $payDataDto->command;
        $header = $payDataDto->commandHeaderDto;
        return $this->performInternetCommand($payAirtimeDto,$header);
    }


    public function getDataFromAmount(float $amount) : float
    {
        return $amount;
    }

    /**
     * @throws \ReflectionException
     * @throws InvalidDataException
     * @throws GeneralException
     */
    public function transactionStatus(TransactionStatusFullDto $param): CommandResultDto
    {
        return $this->performStatusCommand($param->command,$param->commandHeaderDto);
    }

    public function listAccount(): array
    {
        $accountDtos = [];
        foreach ( $this->accountRepository->findAll() as $account){
//            $accountDtos[] = $this->utilService->map()
        }
        return $accountDtos;
    }

    /**
     * @throws InvalidCredentialsException
     * @throws NonUniqueExternalIdException
     * @throws InsufficientBalanceException
     * @throws AccountNotFoundException
     * @throws ParameterNotFoundException
     * @throws InvalidDataException
     * @throws BadPinNumberException
     * @throws InvalidPhoneNumberException
     * @throws \ReflectionException
     */
    public function payNumberAirtime(string $xml, array $headers=[]): CommandResultDto
    {
        $this->checkConnection($headers);
        $commandXml = simplexml_load_string($xml);
        $command = $this->utilService->mapObjectXml($commandXml,Command::class);
        return $this->payAirtime(new PayAirtimeFullDto($command,$headers));
    }


    public function performInternetCommand(Command $command, CommandHeaderDto $header) : CommandResultDto
    {
        $transaction = $this->utilService->map($command,Transaction::class);

        if($transaction instanceof Transaction){
            $transaction->setMsisdn2($command->ACCOUNTNUM);
        }
        $this->checkOperation($command->TYPE,$_ENV['INTERNET_RECHARCHE']);
        $this->checkConnection($header);
        $account =  $this->checkCredentials($header,$transaction);
        $undefined = $this->utilService->getUndefinedParams($command,['TXNID','MSISDN2'],['NOTIFICATION_MSISDN','PASSWORD','EXTCODE']);

        if(!empty($undefined)){
            $message = sprintf(self::BADPARAMETER_FORMAT,$undefined,"");
            throw new GeneralException($message,null,ResponseStatus::INVALID_PARAMETER);
        }

        $this->checkDateFormat($command->DATE);


        $searchNumber = $transaction->getMsisdn2();
        $number = $this->numberRepository->findOneBy([Number::MSISDN=> $searchNumber]);

        if(!$number){
            throw new GeneralException($searchNumber,$transaction,ResponseStatus::INVALID_PHONE_NUMBER);
        }
        $transactionId = null;
        $numberTransaction = null;
        $type = $transaction->getType();
        $transactionId = $this->utilService->generateTransactionId();

        $amount = floatval($command->AMOUNT);
        $this->checkAmount($amount,$transaction);


        if($account instanceof Account){
            $balance = $account->getDatabalance();
            $oldBalance  = $account->getDatanewbalance() ?? $balance;
            $cBalance = $oldBalance - $this->getDataFromAmount($command->AMOUNT);
            if($cBalance < 0){
                throw new GeneralException("",$transaction,ResponseStatus::INSUFFICIENT_BALANCE_NUMBER);
            }
            $account->setDatanewbalance($cBalance);
            $account->setDataoldbalance($oldBalance);

            $transtation  = $this->buildTransaction($transactionId,
                OperationNature::DEBIT->value(),
                $type,
                $oldBalance,
                $cBalance
            );
            $transtation->setMsisdn($account->getMsisdn());
            $transtation->setMsisdn2($number->getMsisdn());
            $transtation->setExtrefnum($command->EXTREFNUM);
            $this->transactionRepository->save($transtation);
            $this->accountRepository->save($account);
        }

        if($number){
            $balance = $number->getNumberdatabalance() ?? 0;
            $oldBalance = $number->getNumberdatanewbalance() ?? $balance;
            $cBalance = floatval($oldBalance) + floatval($command->AMOUNT);
            if($cBalance >= $_ENV['maxBalance']){
            }

            $number->setNumberdatanewbalance(strval($cBalance));
            $number->setNumberdataoldbalance(strval($oldBalance));
            $transaction = $this->buildTransaction($transactionId,
                OperationNature::CREDIT->value(),
                $type,
                $oldBalance,
                $cBalance);
            $number =  $this->numberRepository->save($number);
            $transaction->setMsisdn($number->getMsisdn());
            $transaction->setMsisdn2($account->getMsisdn());
            $transaction->setExtrefnum($command->EXTREFNUM);
            $transaction->setBalancedatanew($number->getNumberdatanewbalance());
            $transaction->setBalancedataold($number->getNumberdataoldbalance());
            $numberTransaction = $this->transactionRepository->save($transaction);
        }

        $result = $this->utilService->map($numberTransaction,CommandResultDto::class,true);

        if($result instanceof CommandResultDto){
            $message = $this->getMessage($command->TYPE);
            $result->DATE = $numberTransaction->getDateEndTransaction()->format('d/m/Y H:i:s');
            $dataCurrency = $this->utilService->getDataCurrency();
            $textMessage = sprintf(
                $message->getMessage(),
                $numberTransaction->getTxnid(),
                $command->AMOUNT,
                $dataCurrency,
                $account->getMsisdn(),
                $command->MSISDN2,
                $numberTransaction->getBalancedataold(),
                $numberTransaction->getBalancedatanew()
            );
            $result->MESSAGE = $textMessage;
        }
        return  $result;
    }

    public function performAirtimeCommand(Command $command, CommandHeaderDto $commandHeaderDto) : CommandResultDto
    {
        $this->checkOperation($command->TYPE,$_ENV['AIRTIME_PAY']);

        $transaction = $this->utilService->map($command,Transaction::class);

        $account = $this->checkCredentials($commandHeaderDto,$transaction);

        $undefined = $this->utilService->getUndefinedParams($command,['ACCOUNTNUM','TXNID'],['LOGINID','PASSWORD','EXTCODE']);

        if(!empty($undefined)){
            $message = sprintf(self::BADPARAMETER_FORMAT,$undefined,"");
            throw new GeneralException($message,null,ResponseStatus::INVALID_PARAMETER);
        }

        $this->checkDateFormat($command->DATE);

        if(!isset($command->AMOUNT)){
            $message = sprintf(self::BADPARAMETER_FORMAT,Number::AMOUNT,"");
            throw new GeneralException($message,$transaction,ResponseStatus::INVALID_PARAMETER);
        }

        $amount = floatval($command->AMOUNT);

        $this->checkAmount($amount,$transaction);

        $number = $this->numberRepository->findOneBy([Number::MSISDN=> $transaction->getMsisdn2()]);

        if(!$number){
            throw new GeneralException(null,$transaction,ResponseStatus::INVALID_PHONE_NUMBER);
        }
        $transactionId = null;
        $numberTransaction = null;
        $type = $transaction->getType();
        if($number){
            $balance = $number->getNumberbalance();
            $oldBalance = $number->getNumbernewbalance() ?? $balance;
            $cBalance = floatval($oldBalance) + floatval($command->AMOUNT);
            if($cBalance >= $_ENV['maxBalance']){
            }

            $number->setNumbernewbalance(strval($cBalance));
            $number->setNumberoldbalance(strval($oldBalance));
            $transactionId = $this->utilService->generateTransactionId();
            $transaction = $this->buildTransaction($transactionId,
                OperationNature::CREDIT->value(),
                $type,
                $oldBalance,
                $cBalance);
            $number =  $this->numberRepository->save($number);
            $transaction->setMsisdn($number->getMsisdn());
            $transaction->setMsisdn2($account->getMsisdn());
            $transaction->setExtrefnum($command->EXTREFNUM);
            $numberTransaction = $this->transactionRepository->save($transaction);
        }

        if($account instanceof Account){
            $balance = $account->getBalance();
            $oldBalance  = $account->getNewbalance() ?? $balance;
            $cBalance = $oldBalance - floatval($command->AMOUNT);
            if($cBalance < 0){
                throw new GeneralException("",$transaction,ResponseStatus::INSUFFICIENT_BALANCE_NUMBER);
            }
            $account->setNewbalance($cBalance);
            $account->setOldbalance($oldBalance);

            $transtation  = $this->buildTransaction($transactionId,
                OperationNature::DEBIT->value(),
                $type,
                $oldBalance,
                $cBalance
            );
            $transtation->setMsisdn($account->getMsisdn());
            $transtation->setMsisdn2($number->getMsisdn());
            $transtation->setExtrefnum($command->EXTREFNUM);
            $this->transactionRepository->save($transtation);
            $this->accountRepository->save($account);
        }

        $result = $this->utilService->map($numberTransaction,CommandResultDto::class,true);

        if($result instanceof CommandResultDto){
            $message = $this->getMessage($command->TYPE);
            $result->DATE = $numberTransaction->getDateEndTransaction()->format('d/m/Y H:i:s');
            $textMessage = sprintf(
                $message->getMessage(),
                $numberTransaction->getTxnid(),
                $command->AMOUNT,
                $account->getCurrency(),
                $account->getMsisdn(),
                $command->MSISDN2,
                $numberTransaction->getBalanceold(),
                $numberTransaction->getBalancenew()
            );
            $result->MESSAGE = $textMessage;
        }

        return $result;
    }


    /**
     * @throws \ReflectionException
     * @throws InvalidDataException
     * @throws GeneralException
     */
    public function performStatusCommand(Command $command, CommandHeaderDto $commandHeaderDto) : CommandResultDto
    {
        $this->checkConnection($commandHeaderDto);
        $this->checkOperation($command->TYPE,$_ENV['TRANSACTION_STATUS']);
        $transaction = $this->utilService->map($command,Transaction::class);
        $account =  $this->checkCredentials($commandHeaderDto,$transaction);
        $txnsid = $this->utilService->generateTransactionId();
        $balance = $account->getBalance() ?? 0;
        $oldBalance =  $account->getOldbalance() ?? 0;
        $newBalance = $account->getNewbalance() ?? 0;
        $ctransaction = $this->buildTransaction(
            $txnsid,
            OperationNature::BALANCE->value(),
            $command->TYPE,
            floatval($oldBalance),
            floatval($newBalance)
        );
        $ctransaction->setExtrefnum($command->EXTREFNUM);
        $ctransaction =  $this->transactionRepository->save($ctransaction);
        $rTrans =null;
        if($transaction instanceof Transaction){
            $rTrans = $this->transactionRepository->findOneBy([Transaction::TXNID => $transaction->getTxnid()]);
        }
        $result  = $this->utilService->map($ctransaction,CommandResultDto::class,true);

        if($result instanceof CommandResultDto){
            $message = $this->getMessage($ctransaction->getType());
            $result->DATE = $ctransaction->getDateEndTransaction()->format('d/m/Y H:i:s');
            $result->MESSAGE = sprintf($message->getMessage(),$transaction->getTxnid(),
                $rTrans?->getTxnStatus() ?? 500
            );
        }

        return  $result;
    }

    /**
     * @throws \ReflectionException
     * @throws InvalidDataException
     * @throws GeneralException
     */
    public function performBalanceCommand(Command $command, CommandHeaderDto $commandHeader) : BalanceResultDto
    {
        $this->checkOperation($command->TYPE,$_ENV['API_BALANCE']);

        $transaction   = $this->utilService->map($command,Transaction::class,toLower: true);

        $account =  $this->checkCredentials($commandHeader,$transaction);

        $undefined = $this->utilService->getUndefinedParams($command,['AMOUNT','ACCOUNTNUM','SELECTOR','LANGUAGE2','LANGUAGE1','TXNID','MSISDN2'],['NOTIFICATION_MSISDN','LOGINID','PASSWORD','EXTCODE']);

        if(!empty($undefined)){
            $message = sprintf(self::BADPARAMETER_FORMAT,$undefined,"");
            throw new GeneralException($message,null,ResponseStatus::INVALID_PARAMETER);
        }

        $this->checkDateFormat($command->DATE);

        $txnsid = $this->utilService->generateTransactionId();
        $transaction = $this->buildTransaction($txnsid,OperationNature::BALANCE->value(),$command->TYPE);
        $transaction->setExtrefnum($command->EXTREFNUM);
        $transaction =  $this->transactionRepository->save($transaction);

        $result = $this->utilService->map($transaction,BalanceResultDto::class,true);
        if($result instanceof BalanceResultDto){
           // $balanceResult = $this->map($result,BalanceResultDto::class,true);
           // $message = $this->getMessage($command->TYPE);
            $result->TYPE = $_ENV['API_RESPONSE'];
            unset($result->MESSAGE);
            unset($result->TXNID);
            $result->DATE = $transaction->getDateEndTransaction()->format('d/m/Y H:i:s');
            //$result->MESSAGE = sprintf($message->getMessage(),$account->getBalance());

            $result->RECORD = new Record();
            $result->RECORD->PRODUCTCODE = ord($account->getExtnwcode());
            $result->RECORD->BALANCE = $account->getBalance();
            $result->RECORD->PRODUCTSHORTNAME = $account->getExtnwcode();

        }
        return $result;
    }


    /**
     * @throws InvalidCredentialsException
     * @throws AccountNotFoundException
     * @throws ParameterNotFoundException
     * @throws InvalidDataException
     * @throws BadPinNumberException
     * @throws \ReflectionException
     * @throws GeneralException
     */
    public function checkBalance(string $command,array $headers=[]): CommandResultDto
    {
        $this->checkConnection($headers);
        $commandXml = simplexml_load_string($command);
        $commandHeader = $this->utilService->mapArray($headers,CommandHeaderDto::class);

        $command = $this->utilService->mapObjectXml($commandXml,Command::class);

        return $this->performBalanceCommand($command,$commandHeader);
    }


    /**
     * @throws GeneralException
     */
    public function getMessage(string $type, int $languageIndex=0) :Message
    {
        $message = $this->messageRepository->findOneBy([Message::TYPE => $type, Message::MESSAGEINDEX => $languageIndex]);
        if(!$message){
            throw new GeneralException("",null,ResponseStatus::NOMESSAGE_SPECIFIED);
        }
        return $message;
    }


    public function newMessage(CommandMessage $commandMessage): CommandResultDto
    {
        $message = $this->utilService->map($commandMessage, Message::class);
        if($message instanceof Message){
            $message->setLanguageid($this->utilService->generateUnique());
        }
        $fmessage = $this->messageRepository->findOneBy(
            [Message::TYPE => $message->getType(), Message::MESSAGEINDEX => $message->getLanguageindex()]
        );

        if($fmessage){
            $fmessage->setMessage($message->getMessage());
            $message = $fmessage;
        }
        $message = $this->messageRepository->save($message);
        $result = $this->utilService->map($message,CommandResultDto::class,true);
        if($result instanceof CommandResultDto){
            $result->TXNSTATUS = 200;
            unset($result->{"EXTREFNUM"});
            unset($result->{"DATE"});
            unset($result->{"TXNID"});
        }
        return $result;
    }

    /**
     * @throws GeneralException
     */
    public function loginAirtimeAccount(AccountCreateDto $createDto): AccountCreateResultDto
    {
        if(!isset($createDto->username)){
            $message = sprintf(self::BADPARAMETER_FORMAT,Account::USERNAME,"");
            throw new GeneralException($message,null,ResponseStatus::INVALID_PARAMETER);
        }

        if(!isset($createDto->password)){
            $message = sprintf(self::BADPARAMETER_FORMAT,Account::PASSWORD,"");
            throw new GeneralException($message,null,ResponseStatus::INVALID_PARAMETER);
        }

        $account = $this->accountRepository->findOneBy([Account::USERNAME => $createDto->username]);
        if(!$account){
            throw new GeneralException($createDto->username,null,ResponseStatus::ACCOUNT_NOT_FOUND);
        }

        if(!$this->passwordHasher->isPasswordValid($account,$createDto->password)){
            throw new GeneralException(null,null,ResponseStatus::INVALID_CREDENTIAL);
        }

        $account =   $this->accountRepository->save($account);
        return $this->map($account,AccountCreateResultDto::class);
    }

    public function executeCommand(ExecuteCommandDto $executeCommandDto): CommandResultDto
    {
        if(!isset($executeCommandDto->command)){
            throw new GeneralException(null,null,ResponseStatus::INVALID_PARAMETER);
        }
        if(!isset($executeCommandDto->commandHeaderDto)){
            throw new GeneralException(null,null,ResponseStatus::INVALID_PARAMETER);
        }

        $command = $executeCommandDto->command;
        $commandHeader = $executeCommandDto->commandHeaderDto;
        $result = null;
        switch ($command->TYPE){
            case $_ENV['API_BALANCE']:{
                $result =  $this->performBalanceCommand($command,$commandHeader);
                break;
            }
            case $_ENV['INTERNET_RECHARCHE']:{
                $result = $this->performInternetCommand($command,$commandHeader);
                break;
            }

            case $_ENV['AIRTIME_PAY']:{
                $result = $this->performAirtimeCommand($command,$commandHeader);
                break;
            }
            case $_ENV['TRANSACTION_STATUS']:{
                $result = $this->performStatusCommand($command,$commandHeader);
                break;
            }
            default:break;
        }

        return  $result;
    }

    public function resetAccountKeys(AccountCreateDto $createDto) : AccountCreateResultDto
    {
        $account  = $this->accountRepository->findOneBy([Account::USERNAME => $createDto->username]);

        if(!$account){
            throw new AccountNotFoundException($createDto->username);
        }
        $clearApiKey = $this->utilService->generateRandom();
        $hashApiKey =   password_hash($clearApiKey,PASSWORD_BCRYPT);
        $account->setApikey($hashApiKey);
        $subcriptionKey = $this->utilService->generateRandom(30);
        $hasSubKey = password_hash($subcriptionKey,PASSWORD_BCRYPT);
        $account->setSubscriptionkey($hasSubKey);
        $account =   $this->accountRepository->save($account);
        $result = $this->map($account,AccountCreateResultDto::class);
        if($result instanceof AccountCreateResultDto){
            $result->apikey = $clearApiKey;
            $result->subscriptionkey = $subcriptionKey;
        }
        return  $result;
    }
}