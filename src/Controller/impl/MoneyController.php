<?php

namespace App\Controller\impl;

use App\Dto\AccountCreateDto;
use App\Dto\AccountMoneyCreateDto;
use App\Dto\PayMoneyDto;
use App\Dto\TokenCreateDto;
use App\Response\AccountMoneyResponse;
use App\Response\MoneyInitResponse;
use App\Response\MoneyPayResponse;
use App\Response\TokenResponse;
use App\Service\MoneyService;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class MoneyController extends AbstractController implements \App\Controller\MoneyController
{
    public function __construct(
        public MoneyService $moneyService
    )
    {

    }

    #[Route(self::CASHOUT_INIT, name: self::CASHOUT_INIT_NAME, methods: [self::POST_METHOD])]
    #[OA\Response(
        response: 200,
        description: 'Init payment cashout',
        //content: new Model(type: MoneyInitResponse::class)
    )]
    #[Security(name: self::X_AUTH_TOKEN)]
    #[Security(name: self::WSO2_Authorization)]
    public function initCashout(): MoneyInitResponse
    {
        return
            new MoneyInitResponse(
                $this->moneyService->init(self::CASHOUT)
            );
    }

    #[Route(self::CASHIN_INIT, name: self::CASHIN_INIT_NAME, methods: [self::POST_METHOD])]
    #[OA\Response(
        response: 200,
        description: 'Init payment cashin',
//        content:  new Model(type: MoneyInitResponse::class)
    )]
    #[Security(name: self::X_AUTH_TOKEN)]
    #[Security(name: self::WSO2_Authorization)]
    public function initCashin(): MoneyInitResponse
    {
        return
            new MoneyInitResponse(
                $this->moneyService->init(self::CASHIN)
            );
    }

    #[Route(self::MP_INIT, name: self::MP_INIT_NAME, methods: [self::POST_METHOD])]
    #[OA\Response(
        response: 200,
        description: 'Init payment merchant payment',
//        content:  new Model(type: MoneyInitResponse::class)
    )]
    #[Security(name: self::X_AUTH_TOKEN)]
    #[Security(name: self::WSO2_Authorization)]
    public function initMp(): MoneyInitResponse
    {
        return
            new MoneyInitResponse(
                $this->moneyService->init(self::MP)
            );
    }

    #[Route(self::CASHOUT_PAY, name: self::CASHOUT_PAY_NAME, methods: [self::POST_METHOD])]
//    #[OA\RequestBody(new Model(type: PayMoneyDto::class))]
    #[OA\Response(
        response: 200,
        description: 'Pay cashout',
//        content:  new Model(type: MoneyPayResponse::class)
    )]
    #[Security(name: self::X_AUTH_TOKEN)]
    #[Security(name: self::WSO2_Authorization)]
    public function payMoneyCashout(#[MapRequestPayload] PayMoneyDto $payMoneyDto): MoneyPayResponse
    {
        return
            new MoneyPayResponse(
                $this->moneyService->pay($payMoneyDto,self::CASHOUT)
            );
    }

    #[Route(self::CASHIN_PAY, name: self::CASHIN_PAY_NAME, methods: [self::POST_METHOD])]
//    #[OA\RequestBody(new Model(type: PayMoneyDto::class))]
    #[OA\Response(
        response: 200,
        description: 'Pay cashin',
//        content:  new Model(type: MoneyPayResponse::class)
    )]
    #[Security(name: self::X_AUTH_TOKEN)]
    #[Security(name: self::WSO2_Authorization)]
    public function payMoneyCashin(#[MapRequestPayload] PayMoneyDto $payMoneyDto): MoneyPayResponse
    {
        return
            new MoneyPayResponse(
                $this->moneyService->pay($payMoneyDto,self::CASHIN)
            );
    }

    #[Route(self::MP_PAY, name: self::MP_PAY_NAME, methods: [self::POST_METHOD])]
//    #[OA\RequestBody(new Model(type: PayMoneyDto::class))]
    #[OA\Response(
        response: 200,
        description: 'Pay merchant payment',
//        content: new Model(type: MoneyPayResponse::class)
    )]
    #[Security(name: self::X_AUTH_TOKEN)]
    #[Security(name: self::WSO2_Authorization)]
    public function payMoneyMp(#[MapRequestPayload] PayMoneyDto $payMoneyDto): MoneyPayResponse
    {
        return
            new MoneyPayResponse(
                $this->moneyService->pay($payMoneyDto,self::MP)
            );
    }

    #[Route(self::MONEY_ACCOUNT_CASHIN_CREATE_URI, name: self::MONEY_ACCOUNT_CASHIN_CREATE_NAME, methods: [self::POST_METHOD])]
//    #[OA\RequestBody(new Model(type: AccountMoneyCreateDto::class))]
    #[OA\Response(
        response: 200,
        description: 'Create a cashin account number',
//        content:  new Model(type: AccountMoneyResponse::class)
    )]
    public function createCashinAccount(#[MapRequestPayload] AccountMoneyCreateDto $accountMoneyCreateDto): AccountMoneyResponse
    {
        return new AccountMoneyResponse(
            $this->moneyService->createMoneyAccount(
               new AccountCreateDto(
                   $accountMoneyCreateDto->username,
                   $accountMoneyCreateDto->password,
                   self::CASHIN
               )
            )
        );
    }


    #[Route(self::MONEY_ACCOUNT_MP_CREATE_URI, name: self::MONEY_ACCOUNT_MP_CREATE_NAME, methods: [self::POST_METHOD])]
//    #[OA\RequestBody(new Model(type: AccountMoneyCreateDto::class))]
    #[OA\Response(
        response: 200,
        description: 'Create a cashout account number',
//        content: new Model(type: AccountMoneyResponse::class)
    )]
    public function createCashoutAccount(#[MapRequestPayload] AccountMoneyCreateDto $accountMoneyCreateDto): AccountMoneyResponse
    {
        return new AccountMoneyResponse(
            $this->moneyService->createMoneyAccount(new AccountCreateDto(
                $accountMoneyCreateDto->username,
                $accountMoneyCreateDto->password,
                self::MP
            ))
        );
    }

    #[Route(self::MONEY_ACCOUNT_CASHOUT_CREATE_URI, name: self::MONEY_ACCOUNT_CASHOUT_CREATE_NAME, methods: [self::POST_METHOD])]
//    #[OA\RequestBody(new Model(type: AccountMoneyCreateDto::class))]
    #[OA\Response(
        response: 200,
        description: 'Create a mp account number',
//        content: new Model(type: AccountMoneyResponse::class)
    )]
    public function createMpAccount(#[MapRequestPayload] AccountMoneyCreateDto $accountMoneyCreateDto): AccountMoneyResponse
    {
        return new AccountMoneyResponse(
            $this->moneyService->createMoneyAccount(new AccountCreateDto(
                $accountMoneyCreateDto->username,
                $accountMoneyCreateDto->password,
                self::CASHOUT
            ))
        );
    }

    #[Route(self::MONEY_TOKEN_URI, name: self::MONEY_TOKEN_NAME,defaults: ["_format"=>"application/x-www-form-urlencoded"], methods: [self::POST_METHOD])]
//    #[OA\RequestBody(new Model(type: TokenCreateDto::class))]
    #[OA\Response(
        response: 200,
        description: 'Generate an orange money (cashin, cashout, merchant payment) token number',
//        content: new Model(type: TokenResponse::class)
    )]
    #[Security(name: 'Basic')]
    public function generateToken(#[MapRequestPayload] TokenCreateDto $tokenCreateDto): TokenResponse
    {
        return new TokenResponse(
            $this->moneyService->generateToken(
                $tokenCreateDto
            )
        );
    }


    #[Route(self::CASHIN_STATUS, name: self::CASHIN_STATUS_NAME, methods: [self::POST_METHOD])]
    #[OA\Response(
        response: 200,
        description: 'Get paytoken status',
//        content: new Model(type: MoneyPayResponse::class)
    )]
    #[Security(name: self::X_AUTH_TOKEN)]
    #[Security(name: self::WSO2_Authorization)]
    public function getStatusCashin(?string $payToken = null): MoneyPayResponse
    {
        return new MoneyPayResponse(
            $this->moneyService->getStatus(self::CASHIN, $payToken)
        );
    }

    #[Route(self::CASHOUT_STATUS, name: self::CASHOUT_STATUS_NAME, methods: [self::GET_METHOD])]
    #[OA\Response(
        response: 200,
        description: 'Get paytoken status',
//        content: new Model(type: MoneyPayResponse::class)
    )]
    #[Security(name: self::X_AUTH_TOKEN)]
    #[Security(name: self::WSO2_Authorization)]
    public function getStatusCashout(?string $payToken = null): MoneyPayResponse
    {
        return new MoneyPayResponse(
            $this->moneyService->getStatus(self::CASHOUT, $payToken)
        );    }


    #[Route(self::MP_STATUS, name: self::MP_STATUS_NAME, methods: [self::GET_METHOD])]
    #[OA\Response(
        response: 200,
        description: 'Get paytoken status',
//        content: new Model(type: MoneyPayResponse::class)
    )]
    #[Security(name: self::X_AUTH_TOKEN)]
    #[Security(name: self::WSO2_Authorization)]
    public function getStatusMp(?string $payToken = null): MoneyPayResponse
    {
        return new MoneyPayResponse(
            $this->moneyService->getStatus(self::MP, $payToken)
        );
    }

    #[Route(self::MONEY_ACCOUNT_LOGIN_URI, name: self::MONEY_ACCOUNT_LOGIN_NAME, methods: [self::GET_METHOD])]
//    #[OA\RequestBody(new Model(type: AccountMoneyCreateDto::class))]
    #[OA\Response(
        response: 200,
        description: 'Login an account',
//        content: new Model(type: AccountMoneyResponse::class)
    )]
    public function loginMoneyAccount(#[MapRequestPayload] AccountMoneyCreateDto $accountMoneyCreateDto): AccountMoneyResponse
    {
        return new AccountMoneyResponse(
            $this->moneyService->loginMoneyAccount(
                new AccountCreateDto(
                    $accountMoneyCreateDto->username,
                    $accountMoneyCreateDto->password,
                    self::CASHIN
                )
            )
        );
    }
}