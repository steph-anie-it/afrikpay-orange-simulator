<?php

namespace App\Controller\impl;

use App\Response\ErrorResponse;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;
use Symfony\Component\Routing\Annotation\Route;


class ErrorController extends AbstractController
{
    public function __construct(protected RequestStack $requestStack)
    {

    }

    #[Route('error', name: 'error', methods: ['GET', 'POST'])]
    public function show(Exception|\TypeError|FlattenException $exception): ErrorResponse
    {
        $result = ["code" => 500,"result" => $exception->getMessage()];
        $errorResponse =  new ErrorResponse($result);
        return $errorResponse;
    }

}