<?php

namespace App\Controller\impl;

use App\Dto\AccountCreateDto;
use App\Dto\AccountMoneyCreateDto;
use App\Dto\PayMoneyDto;
use App\Response\AccountMoneyResponse;
use App\Response\MoneyInitResponse;
use App\Response\MoneyPayResponse;
use App\Service\MoneyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class MoneyController extends AbstractController implements \App\Controller\MoneyController
{
    public function __construct(
        public MoneyService $moneyService
    )
    {

    }

    #[Route(self::CASHOUT_INIT, name: self::CASHOUT_INIT_NAME, methods: [self::POST_METHOD])]
    public function initCashout(): MoneyInitResponse
    {
        return
            new MoneyInitResponse(
                $this->moneyService->init(self::CASHOUT_INIT)
            );
    }

    #[Route(self::CASHIN_INIT, name: self::CASHIN_INIT_NAME, methods: [self::POST_METHOD])]
    public function initCashin(): MoneyInitResponse
    {
        return
            new MoneyInitResponse(
                $this->moneyService->init(self::CASHIN_INIT)
            );
    }

    #[Route(self::MP_INIT, name: self::MP_INIT_NAME, methods: [self::POST_METHOD])]
    public function initMp(): MoneyInitResponse
    {
        return
            new MoneyInitResponse(
                $this->moneyService->init(self::MP_INIT)
            );
    }

    #[Route(self::CASHOUT_PAY, name: self::CASHOUT_PAY_NAME, methods: [self::POST_METHOD])]
    public function payMoneyCashout(#[MapRequestPayload] PayMoneyDto $payMoneyDto): MoneyPayResponse
    {
        return
            new MoneyPayResponse(
                $this->moneyService->pay($payMoneyDto,self::CASHOUT_PAY)
            );
    }

    #[Route(self::CASHIN_PAY, name: self::CASHIN_PAY_NAME, methods: [self::POST_METHOD])]
    public function payMoneyCashin(#[MapRequestPayload] PayMoneyDto $payMoneyDto): MoneyPayResponse
    {
        return
            new MoneyPayResponse(
                $this->moneyService->pay($payMoneyDto,self::CASHIN_PAY)
            );
    }

    #[Route(self::MP_PAY, name: self::MP_PAY_NAME, methods: [self::POST_METHOD])]
    public function payMoneyMp(#[MapRequestPayload] PayMoneyDto $payMoneyDto): MoneyPayResponse
    {
        return
            new MoneyPayResponse(
                $this->moneyService->pay($payMoneyDto,self::MP_PAY)
            );
    }

    #[Route(self::MONEY_ACCOUNT_CASHIN_CREATE_URI, name: self::MONEY_ACCOUNT_CASHIN_CREATE_NAME, methods: [self::POST_METHOD])]
    public function createCashinAccount(#[MapRequestPayload] AccountMoneyCreateDto $accountMoneyCreateDto): AccountMoneyResponse
    {
        return new AccountMoneyResponse(
            $this->moneyService->createMoneyAccount(
               new AccountCreateDto(
                   $accountMoneyCreateDto->username,
                   $accountMoneyCreateDto->password,
                   self::MONEY_ACCOUNT_CASHIN_CREATE_NAME
               )
            )
        );
    }


    #[Route(self::MONEY_ACCOUNT_MP_CREATE_URI, name: self::MONEY_ACCOUNT_MP_CREATE_NAME, methods: [self::POST_METHOD])]
    public function createCashoutAccount(#[MapRequestPayload] AccountMoneyCreateDto $accountMoneyCreateDto): AccountMoneyResponse
    {
        return new AccountMoneyResponse(
            $this->moneyService->createMoneyAccount(new AccountCreateDto(
                $accountMoneyCreateDto->username,
                $accountMoneyCreateDto->password,
                self::MONEY_ACCOUNT_MP_CREATE_NAME
            ))
        );
    }

    #[Route(self::MONEY_ACCOUNT_CASHOUT_CREATE_URI, name: self::MONEY_ACCOUNT_CASHOUT_CREATE_NAME, methods: [self::POST_METHOD])]
    public function createMpAccount(#[MapRequestPayload] AccountMoneyCreateDto $accountMoneyCreateDto): AccountMoneyResponse
    {
        return new AccountMoneyResponse(
            $this->moneyService->createMoneyAccount(new AccountCreateDto(
                $accountMoneyCreateDto->username,
                $accountMoneyCreateDto->password,
                self::MONEY_ACCOUNT_CASHOUT_CREATE_NAME
            ))
        );
    }
}