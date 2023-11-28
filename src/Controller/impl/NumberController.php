<?php

namespace App\Controller\impl;


use App\Controller\NumberController  as INumberController;
use App\Response\NumberGenerateResponse;
use App\Service\AccountService;
use App\Service\BillService;
use App\Service\NumberService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NumberController extends AbstractController implements INumberController
{

    public function __construct(
        protected readonly NumberService $numberService
    ) {
    }
    #[Route(self::GENERATE_URI, name: self::GENERATE_NAME, methods: [self::GENERATE_METHOD])]
    public function generate(): NumberGenerateResponse
    {
        return new NumberGenerateResponse(
            $this->numberService->generateBill()
        );
    }
}