<?php

namespace App\Controller\impl;
use App\Controller\NumberController as INumberController;
use App\Dto\AccountCreateDto;
use App\Dto\AccountCreateResultDto;
use App\Dto\Command;
use App\Dto\PayAirtimeDto;
use App\Dto\PayAirtimeFullDto;
use App\Dto\PayAirtimeResultDto;
use App\Dto\PayDataFullDto;
use App\Dto\TransactionStatusFullDto;
use App\Response\AccountAirtimeResponse;
use App\Response\GenerateAirtimeResponse;
use App\Response\PayAirtimeResponse;
use App\Service\NumberService;
use App\Service\XmlResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function generateNumber(): GenerateAirtimeResponse
    {
        return new GenerateAirtimeResponse(
            $this->numberService->generateNumber()
        );
    }


    #[Route(self::PAY_AIRTIME_URI, name: self::PAY_AIRTIME_NAME, defaults: ["_format"=>"xml/command.dtd"], methods: [self::PAY_AIRTIME_METHOD])]
    public function payAirtime(Request $request,   #[MapRequestPayload] Command $payAirtimeDto): \App\Response\Command
    {
        return new \App\Response\Command(
            $this->numberService->payAirtime(new PayAirtimeFullDto($payAirtimeDto,$request->headers->all()))
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


    #[Route(self::ACCOUNT_AIRTIME_URI, name: self::ACCOUNT_AIRTIME_NAME, methods: [self::ACCOUNT_AIRTIME_METHOD])]
    public function payData(Request $request, \App\Dto\Command $payAirtimeDto): \App\Response\Command
    {
        return new \App\Response\Command(
            $this->numberService->payData(new PayDataFullDto($payAirtimeDto,$request->headers->all()))
        );
    }


    #[Route(self::ACCOUNT_AIRTIME_URI, name: self::ACCOUNT_AIRTIME_NAME, methods: [self::ACCOUNT_AIRTIME_METHOD])]
    public function transactionStatus(Request $request,\App\Dto\Command $payAirtimeDto): \App\Response\Command
    {
        return new \App\Response\Command(
            $this->numberService->transactionStatus(new TransactionStatusFullDto($payAirtimeDto,$request->headers->all()))
        );
    }
}