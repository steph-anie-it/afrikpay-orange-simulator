<?php

namespace App\Service\impl;

use App\Controller\MoneyController;
use App\Dto\AccountCreateDto;
use App\Dto\AccountCreateResultDto;
use App\Dto\AccountMoneyCreateResultDto;
use App\Dto\AccountMoneyLoginResultDto;
use App\Dto\InitMoneyResultDto;
use App\Dto\MoneyCallbackDto;
use App\Dto\PayMoneyDataResultDto;
use App\Dto\PayMoneyDto;
use App\Dto\PayMoneyResultDto;
use App\Dto\PayTokenDto;
use App\Dto\TokenCreateDto;
use App\Dto\TokenDto;
use App\Entity\Account;
use App\Entity\Number;
use App\Entity\Transaction;
use App\Exception\ExceptionList;
use App\Exception\GeneralException;
use App\Exception\InvalidCredentialsException;
use App\Exception\InvalidMoneyCredentialsException;
use App\Exception\MoneyPayException;
use App\Exception\MoneyStatusException;
use App\Model\ResponseStatus;
use App\Repository\AccountRepository;
use App\Repository\NumberRepository;
use App\Repository\TransactionRepository;
use App\Service\HttpService;
use App\Service\MoneyService;
use App\Service\NumberService;
use App\Service\UtilService;
use DateInterval;
use DateTime;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MoneyServiceImpl implements MoneyService
{
    public const BADPARAMETER_FORMAT="%s,%s";

    public const BADTRHREEPARAMETER_FORMAT="%s,%s,%s";

    public const PASSWORD_GRANT_TYPE = 'password';
    public const START_DATE = 'startDate';
    public const END_DATE = 'endDate';

    public const USERNAME = "username";

    public const DATE = 'date';

    public const SUBSCRIBER_NUMBER = "subscriberNumber";
    public const SUBSCRIBER_NAME = "subscriberName";
    public const CHANNEL_NUMBER = "channelNumber";
    public const CHANNEL_NAME="channelName";
    public const AMOUNT = "amount";
    public const PAY_TOKEN = "payToken";
    public const FEES = "fees";
    public const COMMISSION = "commission";
    public const NET_AMOUNT = "netAmount";
    public const NEW_BALANCE = "newBalance";
    public const MESSAGE_REGEX = "/\{\{\w*\}\}/";

    public function __construct(
        public RequestStack $requestStack,
        public NumberService $numberService,
        protected AccountRepository $accountRepository,
        protected NumberRepository $numberRepository,
        protected UserPasswordHasherInterface  $passwordHasher,
        protected TransactionRepository $transactionRepository,
        protected JWTTokenManagerInterface $JWTManager,
        protected HttpService $httpService,
        protected LoggerInterface $logger,
        protected JWSProviderInterface $JWSProvider,
        public UtilService $utilService)
    {

    }


    public function checkCredentials() : void
    {
        $request = $this->requestStack->getCurrentRequest();
        $xAuthToken =  $request->headers->get(self::X_AUTH_TOKEN);

        if (!$xAuthToken){
            throw new InvalidMoneyCredentialsException(exceptionValues: ExceptionList::INVALID_XAUTH_TOKEN);
        }
        $value = base64_decode($xAuthToken);
        $cred =  explode(':',$value);
        if (sizeof($cred) != 2){
            throw new InvalidMoneyCredentialsException(
                $cred[0],
                exceptionValues: ExceptionList::INVALID_TOKEN_CLIENT_ID
            );
        }

        $username = $cred[0];
        $account = $this->accountRepository->findOneBy(['username' => $username]);
        if (!$account){
            throw new InvalidMoneyCredentialsException(
                $username,
                exceptionValues: ExceptionList::UNKNOW_USER
            );
        }
        $password = $cred[1];

        if (!$this->passwordHasher->isPasswordValid($account,$password)){
            throw new InvalidMoneyCredentialsException(
                $username,
                exceptionValues: ExceptionList::INVALID_CREDENTIALS
            );
        }


        $authorization = $request->headers->get(self::WSO2_AUTHORIZATION);
        $wsoAutorization = substr($authorization,strlen(self::BEARER),strlen($authorization));

        if (!$wsoAutorization){
            throw new InvalidMoneyCredentialsException(
                "",
                exceptionValues: ExceptionList::INVALID_WSO2_TOKEN
            );
        }

        try{
            $tokenValues = $this->JWTManager->parse($wsoAutorization);
        }catch (\Throwable $throwable){
            if ($throwable instanceof JWTDecodeFailureException){
               if ($throwable->getReason() != JWTDecodeFailureException::EXPIRED_TOKEN)
               {
                   throw new InvalidMoneyCredentialsException(
                       exceptionValues: ExceptionList::INVALID_USER_JWT_TOKEN
                   );
               }
            }
        }
        /*
        if (!array_key_exists(self::USERNAME,$tokenValues)){
            throw new InvalidMoneyCredentialsException(
                $username,
                exceptionValues: ExceptionList::BAD_WSO2_TOKEN
            );
        }*/

//        $username = $tokenValues[self::USERNAME];
//        $account = $this->accountRepository->findOneBy(['username' => $username]);
//
//        if (!$account){
//            throw new InvalidMoneyCredentialsException(
//                $username,
//                exceptionValues: ExceptionList::BAD_WSO2_TOKEN
//            );
//        }
//        if ($account->getToken() != $wsoAutorization){
//            throw new InvalidMoneyCredentialsException(
//                exceptionValues: ExceptionList::INVALID_USER_JWT_TOKEN
//            );
//        }
    }

    public function init(?string $key = null): InitMoneyResultDto
    {
        $this->checkCredentials();
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
        if(($amount < $minAmount)){
            $message = sprintf(self::BADTRHREEPARAMETER_FORMAT,strval($amount),strval($minAmount),strval($maxAmount));
            throw new GeneralException($message,$transaction,ResponseStatus::INVALID_AMOUNT_MIN_MAX);
        }


        if(($amount > $maxAmount)){
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

    public const PAIEMENT_MESSAGE_TEMPLATE = '%s %s %s from %s to %s  Payment %s done successfully. Old balance %s new balance %s';

    public function pay(PayMoneyDto $payMoneyDto,?string $key = null): PayMoneyResultDto
    {
        $this->checkCredentials();
        $payMoneyDataResultDto = $this->utilService->map($payMoneyDto,PayMoneyDataResultDto::class);

        if (!filter_var($payMoneyDto->notifUrl, FILTER_VALIDATE_URL)){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_URL,
                payMoneyDataResultDto: $payMoneyDataResultDto
            );
        }


        $orderId = $payMoneyDto->orderId;

        if (!$orderId){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_ORDER_ID,
                payMoneyDataResultDto: $payMoneyDataResultDto
            );
        }

        $channel = $payMoneyDataResultDto->channelUserMsisdn;

        if (!$channel){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_CHANNEL_NUMBER,
                payMoneyDataResultDto: $payMoneyDataResultDto
            );
        }

        $account = $this->accountRepository->findOneBy([Account::ACCOUNT_MSISDN => $channel]);

        if (!$account){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::ACCOUNT_NOT_FOUND,
                payMoneyDataResultDto: $payMoneyDataResultDto
            );
        }

        if ($account->getOperationtype() != $key){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_ACCOUNT_CHANNEL_JWT_TOKEN,
                payMoneyDataResultDto: $payMoneyDataResultDto
            );
        }


        if ($account->getPin() != $payMoneyDto->pin){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_PIN_NUMBER,
                payMoneyDataResultDto: $payMoneyDataResultDto
            );
        }

        $msisdn = $payMoneyDto->subscriberMsisdn;
        if (!$msisdn){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_SUBSCRIBER_NUMBER,
                payMoneyDataResultDto: $payMoneyDataResultDto
            );
        }

        $number = $this->numberRepository->findOneBy([Number::MSISDN => $msisdn]);
        if (!$number){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::UNKNOWN_MONEY_NUMBER,
                payMoneyDataResultDto: $payMoneyDataResultDto
            );
        }

        if (!$number->getIsMoney()){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_MONEY_NUMBER,
                payMoneyDataResultDto: $payMoneyDataResultDto
            );
        }

        $transaction = $this->transactionRepository->findOneBy(
            ['paytoken' => $payMoneyDto->payToken,
             'moneytype' => $key
            ]);

        if (!$transaction){
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_PAY_TOKEN_NUMBER,
                payMoneyDataResultDto: $payMoneyDataResultDto
            );
        }


        if ($transaction->getStatus() != Transaction::PENDING){
            $transaction->setStatus(Transaction::FAILED);
            $transaction->setErrorMessage(ExceptionList::INVALID_PAY_TOKEN_TRANSACTION_NUMBER[ExceptionList::MESSAGE]);
            $this->transactionRepository->save($transaction);
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_PAY_TOKEN_TRANSACTION_NUMBER,
                payMoneyDataResultDto: $payMoneyDataResultDto
            );
        }

        try{
            $this->checkAmount(floatval($payMoneyDto->amount));
        }catch (GeneralException $generalException){
            $transaction->setStatus(Transaction::FAILED);
            $transaction->setErrorMessage(ExceptionList::INVALID_AMOUNT[ExceptionList::MESSAGE]);
            $this->transactionRepository->save($transaction);
            throw new MoneyPayException(
                exceptionValues: ExceptionList::INVALID_AMOUNT,
                payMoneyDataResultDto: $payMoneyDataResultDto
            );
        }

        $fees = $this->getFees($account);
        $commission = $this->getCommission($account);

        $amount = floatval($payMoneyDto->amount) + $fees;

        $accountBalance =  $account->getBalance();
        $numberBalance = $number->getNumbernewbalance();
        $moneyType = $transaction->getMoneytype();
        if (in_array($moneyType,[MoneyController::CASHOUT])) {
            $this->checkBalance($amount,$accountBalance,$transaction,$payMoneyDataResultDto);
            $newAccountBalance = $accountBalance + $amount + $commission ;
            $newNumberBalance = $numberBalance - $amount;
            $balance = $newAccountBalance;
        }else if(in_array($moneyType,[MoneyController::MP])){
            $numberBalance = $number->getNumbernewbalance();
            $newAccountBalance = $accountBalance + $amount ;
            $newNumberBalance = $numberBalance - $amount;
            $this->checkBalance($amount,$numberBalance,$transaction,$payMoneyDataResultDto);
            $balance = $newAccountBalance;
        }
        else{
            $numberBalance = $number->getNumbernewbalance();
            $newAccountBalance = $accountBalance + $amount ;
            $newNumberBalance = $numberBalance - $amount;
            $this->checkBalance($amount,$newNumberBalance,$transaction,$payMoneyDataResultDto);
            $balance = $newAccountBalance;
        }

            $account->setOldbalance($accountBalance);
            $account->setNewbalance($newAccountBalance);
            $account->setBalance($newAccountBalance);

            $number->setNumberbalance($newNumberBalance);
            $number->setNumbernewbalance($newNumberBalance);

            $transaction->setBalanceold($balance);
            $transaction->setFees($fees);
            $transaction->setCommission($commission);
            $account = $this->accountRepository->save($account);

        $transaction->setMsisdn($payMoneyDto->subscriberMsisdn);
        $transaction->setCustomerName($number->getCustomername());
        $transaction->setNotifUrl($payMoneyDto->notifUrl);
        $transaction->setAmount($amount);
        $transaction->setStatus('SUCCESSFULL');
        $txnidValue = $this->utilService->generateTransactionId();
        $transaction->setTxnid($txnidValue);
        $transaction->setBalance($account->getBalance());
        $transaction->setPin($account->getPin());
        $transaction->setAccountnumber($account->getMsisdn());
        $transaction->setAccountName($account->getUsername());
        $transaction->setAmount($payMoneyDto->amount);
        $transaction->setMsisdn($payMoneyDto->subscriberMsisdn);
        $transaction->setAccountnumber($payMoneyDto->channelUserMsisdn);
        $transaction->setAccountName($account->getUsername());
        $transaction->setNotifUrl($payMoneyDto->notifUrl);
        $transaction = $this->transactionRepository->save($transaction);
        $number = $this->numberRepository->save($number);
        $payMoneyDataResultDto->status = $transaction->getStatus();
        $payMoneyDataResultDto = $this->buildPayMoneyResultDto($transaction,$key,$payMoneyDataResultDto);
        try{
            $callBackDto = $this->utilService->map($payMoneyDataResultDto->data,MoneyCallbackDto::class);
            $this->httpService->callBack($callBackDto,$payMoneyDto->notifUrl);
        }catch (\Throwable $throwable){
            $this->logger->critical($throwable->getMessage());
        }
        return $payMoneyDataResultDto;
    }


    public function checkBalance(float $amount, float $balance, Transaction $transaction,PayMoneyDataResultDto $payMoneyDataResultDto)
    {
        if ($amount > $balance){
            $transaction->setStatus(Transaction::FAILED);
            $transaction->setErrorMessage(ExceptionList::NOT_ENOUGH_FUND[ExceptionList::MESSAGE]);
            $this->transactionRepository->save($transaction);
            throw new MoneyPayException(
                exceptionValues: ExceptionList::NOT_ENOUGH_FUND,
                payMoneyDataResultDto: $payMoneyDataResultDto
            );
        }
    }

    public function buildPayMoneyResultDto(Transaction $transaction, string $key, ?PayMoneyDataResultDto $payMoneyDataResultDto = null): PayMoneyResultDto
    {
        if (!$payMoneyDataResultDto){
            $payMoneyDataResultDto = new PayMoneyDataResultDto();
        }

        $inittxnstatus = 'inittxnstatus';
        $inittxnmessage = 'inittxnmessage';
        $txnid = 'txnid';
        $confirmtxnmessage = 'confirmtxnmessage';
        $confirmtxnstatus = 'confirmtxnstatus';
        $txnmode = 'txnmode';
        $txnstatus = 'txnstatus';
        $txnmessage = 'txnmessage';

        $payMoneyDataResultDto->createtime = time();
        $payMoneyDataResultDto->amount = floatval($transaction->getAmount());
        $payMoneyDataResultDto->subscriberMsisdn = $transaction->getMsisdn();
        $payMoneyDataResultDto->channelUserMsisdn = $transaction->getAccountnumber();
        $payMoneyDataResultDto->status = $transaction->getStatus();
        $payMoneyDataResultDto->payToken = $transaction->getPaytoken();
        $payMoneyDataResultDto->notifUrl = $transaction->getNotifUrl();
        $payMoneyDataResultDto->$txnstatus = 'SUCCESSFULL';
        $message = "";
        if ($key == MoneyController::CASHIN){
           $message = $this->getTransactionMessage($key,$transaction);
           $payMoneyDataResultDto->$txnmessage = $message;
        }
        $confirmmessage = "";
        $initmessage = "";
        if ($key == MoneyController::CASHOUT || $key == MoneyController::MP){
            $message = $this->getConfirmTransactionMessage($key,$transaction);
            $initmessage = $this->getInitTransactionMessage($key,$transaction);
            $payMoneyDataResultDto->$confirmtxnmessage = $message;
            $payMoneyDataResultDto->$inittxnmessage = $initmessage;
        }

        $payMoneyDataResultDto->$inittxnstatus = 200;
        $payMoneyDataResultDto->$txnid = $transaction->getTxnid();

        $payMoneyDataResultDto->$confirmtxnstatus = "SUCCESSFULL";
        $payMoneyDataResultDto->$txnmode = 'SUCCESSFULL';
        return new PayMoneyResultDto(
            $payMoneyDataResultDto,
            $message
        );
    }

    public function getInitTransactionMessage(string $key, Transaction $transaction) : ?string{
        $messageTemplate = "";
//        "Retrait d'argent reussi par le 698054913. Informations detaillees : Montant: 100 FCFA, Frais: 150 FCFA, No de transaction CO241127.1218.C19143, montant net debite 250 FCFA, Nouveau solde: 200 FCFA."

//        CASHOUT_TXN_MESSAGE="Retrait d'argent reussi par le {{subscriberNumber}}. Informations detaillees : Montant: {{amount}} FCFA, Frais: {{fees}} FCFA, No de transaction {{payToken}}, montant net debite {{netAmount}} FCFA, Nouveau solde: {{newBalance}} FCFA."
        $converter = [];
//        MP_INITTXN_MESSAGE="Paiement e la clientele done.The devrez confirmer le paiement en saisissant son code PIN et vous recevrez alors un SMS. Merci dutiliser des services Orange Money"
//        MP_INITTXN_MESSAGE="Paiement e la clientele done.The devrez confirmer le paiement en saisissant son code PIN et vous recevrez alors un SMS. Merci dutiliser des services Orange Money"
        switch ($key){
            case MoneyController::MP:{
                $messageTemplate = $_ENV['MP_INITTXN_MESSAGE'];
                break;
            }
            case MoneyController::CASHOUT:{
                $converter = [   self::PAY_TOKEN => Transaction::PAY_TOKEN,
                    self::SUBSCRIBER_NUMBER => Transaction::MSISDN,
                    self::NEW_BALANCE  => Transaction::BALANCE,
                    self::FEES => Transaction::FEES,
                    self::NET_AMOUNT => Transaction::AMOUNT
                ];
                $messageTemplate = $_ENV['CASHOUT_TXN_MESSAGE'];
                break;
            }
        }

        return $this->buildMessage($transaction,$messageTemplate,$converter);
    }


    public function getConfirmTransactionMessage(string $key , Transaction $transaction):?string{
        $messageTemplate = "";
        $converter = [];
        switch ($key){
            case MoneyController::MP:{
                $converter = [   self::PAY_TOKEN => Transaction::PAY_TOKEN,
                    self::SUBSCRIBER_NUMBER => Transaction::MSISDN,
                    self::SUBSCRIBER_NAME => Transaction::CUSTOMER_NAME,
                    self::CHANNEL_NAME  => Transaction::ACCOUNT_NAME,
                    self::CHANNEL_NUMBER  => Transaction::ACCOUNT_NUMBER,
                    self::NEW_BALANCE  => Transaction::BALANCE,
                    self::FEES => Transaction::FEES,
                    self::COMMISSION => Transaction::COMMISSION,
                    self::NET_AMOUNT => Transaction::AMOUNT
                ];
                $messageTemplate = $_ENV['MP_CONFIRM_MESSAGE'];
                break;
            }
            case MoneyController::CASHOUT:{
                $converter = [   self::PAY_TOKEN => Transaction::PAY_TOKEN,
                    self::SUBSCRIBER_NUMBER => Transaction::MSISDN,
                    self::SUBSCRIBER_NAME => Transaction::CUSTOMER_NAME,
                    self::CHANNEL_NAME  => Transaction::ACCOUNT_NAME,
                    self::CHANNEL_NUMBER  => Transaction::ACCOUNT_NUMBER,
                    self::NEW_BALANCE  => Transaction::BALANCE,
                    self::FEES => Transaction::FEES,
                    self::COMMISSION => Transaction::COMMISSION,
                    self::NET_AMOUNT => Transaction::AMOUNT
                ];
                $messageTemplate = $_ENV['CASHOUT_CONFIRM_MESSAGE'];
                break;
            }
        }

        return $this->buildMessage($transaction,$messageTemplate,$converter);
    }



    public function getTransactionMessage(string $key, Transaction $transaction):?string{
        $frenchMessage = $_ENV['CASHIN_TXN_MESSAGE_FR'];
        $englisMessage = $_ENV['CASHIN_TXN_MESSAGE_EN'];
        $messages = [$frenchMessage,$englisMessage];
        $message = $messages[rand(0,1)];
        $converter = [   self::PAY_TOKEN => Transaction::PAY_TOKEN,
            self::SUBSCRIBER_NUMBER => Transaction::MSISDN,
            self::SUBSCRIBER_NAME => Transaction::CUSTOMER_NAME,
            self::CHANNEL_NAME  => Transaction::ACCOUNT_NAME,
            self::CHANNEL_NUMBER  => Transaction::ACCOUNT_NUMBER,
            self::NEW_BALANCE  => Transaction::BALANCE,
            self::FEES => Transaction::FEES,
            self::COMMISSION => Transaction::COMMISSION,
            self::NET_AMOUNT => Transaction::AMOUNT
        ];
        return $this->buildMessage($transaction,$message,$converter);
    }

    public function buildMessage(Transaction $transaction,string $template,array $converter = []): string{
        $message = $template;
        if (preg_match_all(self::MESSAGE_REGEX,$template,$matches)){
            $params = $matches[0];
            $result = array_map(function ($val) {
                return str_replace(["{{","}}"],[''],$val);
            },$params);
            $mapp = $this->utilService->mapObjecToArray($transaction,$result,$converter);
            $message =  str_replace(array_merge($result,["{{","}}"]),$mapp,$message);
        }
        return $message;
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

    public function checkTokenCredentials(Account $account){

        $request = $this->requestStack->getCurrentRequest();

        $authorization = $request->headers->get(self::AUTHORIZATION);
        $basicAuth = substr($authorization,strlen(self::BASIC),strlen($authorization));
        $clear = base64_decode($basicAuth);
        $credentials = explode(':',$clear);
        if (sizeof($credentials) != 2){
            throw new InvalidMoneyCredentialsException(
                $credentials[0],
                exceptionValues: ExceptionList::INVALID_TOKEN_CLIENT_ID
            );
        }
        $apiKey = $credentials[0];
        $subscriptionKey = $credentials[1];

        if (!password_verify($apiKey,$account->getApikey())){
            throw new InvalidMoneyCredentialsException(
                $apiKey,
                exceptionValues: ExceptionList::INVALID_APIKEY_TYPE
            );
        }

        if (!password_verify($subscriptionKey,$account->getSubscriptionkey())){
            throw new InvalidMoneyCredentialsException(
                $subscriptionKey,
                exceptionValues: ExceptionList::INVALID_SUBSCRIPTION_KEY_TYPE
            );
        }
    }



    public function generateToken(TokenCreateDto $tokenCreateDto): TokenDto
    {
        if ($tokenCreateDto->grant_type != self::PASSWORD_GRANT_TYPE){
            throw new InvalidMoneyCredentialsException(
                exceptionValues: ExceptionList::INVALID_GRANT_TYPE
            );
        }
        $account  = $this->accountRepository->findOneBy(['username' => $tokenCreateDto->username]);
        if (!$account){
            throw new InvalidMoneyCredentialsException(
                $tokenCreateDto->username,
                exceptionValues: ExceptionList::UNKNOW_USER
            );
        }

        $this->checkTokenCredentials($account);

        if(!$this->passwordHasher->isPasswordValid($account,$tokenCreateDto->password)){
            throw new InvalidMoneyCredentialsException(
                exceptionValues: ExceptionList::INVALID_CREDENTIALS
            );
        }

        $endDate = new \DateTime();
        $tokenDuration = $_ENV['TOKEN_DURATION'];
        $endDate->add(DateInterval::createFromDateString(sprintf("%s %s",$tokenDuration,'seconds')));
        $token =  $this->JWTManager->create($account);
        $array = $this->JWTManager->parse($token);
        $expiry = $array['exp'];
        $start = $array['iat'];
        $total = $expiry - $start;
        $refreshToken = $this->utilService->guidv4();
        $account->setToken($token);
        $this->accountRepository->save($account);
        return new TokenDto(
            access_token:  $token,
            refresh_token: $refreshToken,
            expires_in: $total
        );
    }

    public function getStatus(string $key ,?string $payToken = null): PayMoneyResultDto
    {
        $this->checkCredentials();
        if (!$payToken){
            throw new MoneyStatusException(
                exceptionValues: ExceptionList::PAY_TOKEN_NOT_PROVIDED
            );
        }
        $transaction = $this->transactionRepository->findOneBy(
            ['paytoken' => $payToken,
                'moneytype' => $key
            ]);

        if (!$transaction){
            throw new MoneyStatusException(
                exceptionValues: ExceptionList::PAY_TOKEN_NOT_FOUND
            );
        }

        return $this->buildPayMoneyResultDto($transaction,$key);
    }


    public function loginMoneyAccount(AccountCreateDto $createDto): AccountMoneyLoginResultDto
    {
        $result = $this->numberService->loginAirtimeAccount($createDto);
        return $this->utilService->map($result,AccountMoneyLoginResultDto::class);
    }

    public function regenerateKeyAccount(AccountCreateDto $createDto) : AccountMoneyCreateResultDto{
        $result = $this->numberService->resetAccountKeys($createDto);
        return $this->utilService->map($result,AccountMoneyCreateResultDto::class);
    }

    public function getCommission(Account $account) :float
    {
        return 0;
    }
}