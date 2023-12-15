<?php

namespace App\Controller\impl;
use App\Controller\NumberController as INumberController;
use App\Dto\AccountCreateDto;
use App\Dto\AccountCreateResultDto;
use App\Dto\Command;
use App\Dto\CommandMessage;
use App\Dto\PayAirtimeDto;
use App\Dto\PayAirtimeFullDto;
use App\Dto\PayAirtimeResultDto;
use App\Dto\PayDataDto;
use App\Dto\PayDataFullDto;
use App\Dto\TransactionStatusFullDto;
use App\Response\AccountAirtimeResponse;
use App\Response\GenerateAirtimeResponse;
use App\Response\PayAirtimeResponse;
use App\Service\NumberService;
use App\Service\XmlResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class NumberController extends AbstractController implements INumberController
{

    public function __construct(protected NumberService $numberService){

    }

    #[Route(self::GENERATE_URI, name: self::GENERATE_NAME, methods: [self::GENERATE_METHOD])]
    public function generateNumber(#[MapQueryParameter] ?string $number=null): GenerateAirtimeResponse
    {
        return new GenerateAirtimeResponse(
            $this->numberService->generateNumber($number)
        );
    }


    #[Route(self::PAY_AIRTIME_URI, name: self::PAY_AIRTIME_NAME, defaults: ["_format"=>"xml/command.dtd"], methods: [self::PAY_AIRTIME_METHOD])]
    public function payAirtime(Request $request,   #[MapRequestPayload] Command $payAirtimeDto): \App\Response\Command
    {
        return new \App\Response\Command(
            $this->numberService->payAirtime(new PayAirtimeFullDto($payAirtimeDto,$request->query->all()))
        );
    }



    #[Route(self::PAY_NUMBER_AIRTIME_URI, name: self::PAY_NUMBER_AIRTIME_NAME, defaults: ["_format"=>"xml/command.dtd"], methods: [self::PAY_NUMBER_AIRTIME_METHOD])]
    public function payNumberAirtime(Request $request): \App\Response\Command
    {
        return new \App\Response\Command(
            $this->numberService->payNumberAirtime($request->getContent(),$request->query->all())
        );
    }

//    #[Route(self::PAY_AIRTIME_URI, name: self::PAY_AIRTIME_NAME, methods: [self::PAY_AIRTIME_METHOD])]
//    public function payeAirtime(Request $request): PayAirtimeResponse
//    {
//        dd($request->getPayload());
//        return new PayAirtimeResponse(
//            $this->numberService->payeAirtime($request->getContent())
//        );
//    }

    #[Route(self::ACCOUNT_AIRTIME_URI, name: self::ACCOUNT_AIRTIME_NAME, methods: [self::ACCOUNT_AIRTIME_METHOD])]
    public function createAccount(#[MapRequestPayload] AccountCreateDto $createDto): AccountAirtimeResponse
    {
      return new AccountAirtimeResponse(
          $this->numberService->createAirtimeAccount($createDto)
      );
    }


    #[Route(self::PAY_DATA_URI, name: self::PAY_DATA_NAME, methods: [self::PAY_DATA_METHOD])]
    public function payData(Request $request): \App\Response\Command
    {
        return new \App\Response\Command(
            $this->numberService->payInternetData(new PayDataDto($request->getContent(),$request->query->all()))
        );
    }


    /**
     * @throws \ReflectionException
     */
    #[Route(self::TRANSACTION_STATUS_URI, name: self::TRANSACTION_STATUS_NAME, methods: [self::TRANSACTION_STATUS_METHOD])]
    public function transactionStatus(Request $request,\App\Dto\Command $payAirtimeDto): \App\Response\Command
    {
        return new \App\Response\Command(
            $this->numberService->transactionStatus(new TransactionStatusFullDto($request->getContent(),$request->query->all()))
        );
    }


    #[Route(self::ACCOUNT_LIST_URI, name: self::ACCOUNT_LIST_NAME, methods: [self::ACCOUNT_LIST_METHOD])]
    public function listAccount(): Response
    {
        return new Response(json_encode($this->numberService->listAccount())
        );
    }


    #[Route(self::API_BALANCE_URI, name: self::API_BALANCE_NAME, methods: [self::API_BALANCE_METHOD])]
    public function checkBalance(Request $request): \App\Response\Command
    {
        return new \App\Response\Command(
            $this->numberService->checkBalance($request->getContent(),$request->query->all())
        );
    }

    /*
    #[Route(self::ACCOUNT_LIST_URI, name: self::ACCOUNT_LIST_NAME, methods: [self::ACCOUNT_LIST_METHOD])]
    public function listAccount(): Response
    {
        return new Response(json_encode($this->numberService->listAccount())
        );
    }*/

    #[Route(self::API_MESSAGE_NEW_URI, name: self::API_MESSAGE_NEW_NAME , defaults: ["_format"=>"xml/command.dtd"], methods: [self::API_MESSAGE_NEW_METHOD])]
    public function newMessage(#[MapRequestPayload] CommandMessage $commandMessage): \App\Response\Command
    {
        return new \App\Response\Command(
            $this->numberService->newMessage($commandMessage)
        );
    }


    #[Route(self::ACCOUNT_AIRTIME_LOGIN_URI, name: self::ACCOUNT_AIRTIME_LOGIN_NAME , methods: [self::ACCOUNT_AIRTIME_LOGIN_METHOD])]
    public function loginAccount(#[MapRequestPayload] AccountCreateDto $createDto): AccountAirtimeResponse
    {
        return new AccountAirtimeResponse(
            $this->numberService->loginAirtimeAccount($createDto)
        );
    }
}