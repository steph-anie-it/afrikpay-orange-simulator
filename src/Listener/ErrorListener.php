<?php

namespace App\Listener;

use App\Exception\GeneralException;
use App\Response\Command;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class ErrorListener implements EventSubscriberInterface
{

    public function __construct(private RouterInterface   $router,
                                protected LoggerInterface $logger
        , protected RequestStack                          $requestStack)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onKernelException', 0],
            ]
        ];
    }

    public function onKernelException(ExceptionEvent $exceptionEvent)
    {
        $message = "Error code %s  message %s occurs";
        $throwable = $exceptionEvent->getThrowable();
        $code = strval($throwable->getCode());
        $txnid = null;

        $txttype = null;
        $extRefNum = null;
//        dd($throwable);
        $messageFormat  = "Code %s message %s file %s line %s";
        $message = $throwable->getMessage();
        if($throwable instanceof GeneralException){
            $code = $throwable->getResponseStatus()->code();
            $message = $throwable->getResponseStatus()->message();
        }

        if(is_subclass_of($throwable,GeneralException::class)){
            $transaction = $throwable->getTransaction();
            if($transaction){
                $txttype = $transaction->getType();
                $code = $transaction->getTxnStatus();
                $txnid = $transaction->getTxnid();
                $extRefNum = $transaction->getExtrefnum();
            }
            $code = $throwable->getResponseStatus()->code();
            $message = $throwable->getResponseStatus()->message();
        }

//        $this->logger->info(sprintf($message, $throwable->getCode(), $throwable->getMessage()));

        $errorMessage = sprintf($messageFormat, $code,
            $message,
            $throwable->getFile(),$throwable->getLine());

        $this->logger->critical($errorMessage);

        //$exceptionEvent->allowCustomResponseCode();
        $commandResult = new \App\Dto\Result\CommandResultDto();

        if($txnid){
            $commandResult->TXNID = $txnid;
        }

        if($txttype){
            $commandResult->TYPE = $txttype;
        }

        if($extRefNum){
            $commandResult->EXTREFNUM = $extRefNum;
        }
        dd($throwable);
        $commandResult->TXNSTATUS = $code;
        $date = new \DateTime();
        $commandResult->DATE = $date->format('Y/m/d H:i:s');
        $commandResult->MESSAGE  = $message;
        $response = new Command($commandResult);
        $response->setStatusCode(200);
        $exceptionEvent->setResponse($response);
    }
}