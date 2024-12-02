<?php

namespace App\Listener;

use App\Dto\MoneyCallbackDto;
use App\Dto\PayMoneyDataResultDto;
use App\Dto\PayMoneyResultDto;
use App\Dto\TokenExceptionDto;
use App\Exception\ExceptionList;
use App\Exception\GeneralException;
use App\Exception\InvalidMoneyCredentialsException;
use App\Exception\MoneyPayException;
use App\Exception\MoneyStatusException;
use App\Response\Command;
use App\Response\MoneyPayResponse;
use App\Response\TokenResponse;
use App\Service\HttpService;
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
                                protected LoggerInterface $logger,
                                protected HttpService $httpService,
                                protected RequestStack $requestStack)
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
        $messageFormat  = "Code %s message %s file %s line %s";
        $message = $throwable->getMessage();
        if($throwable instanceof GeneralException){
            $code = $throwable->getResponseStatus()->code();
            $message = $throwable->getResponseStatus()->getMessage($message);
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
            $message = $throwable->getResponseStatus()->getMessage($message);
        }
        if ($throwable instanceof MoneyPayException){
            $message = "inittxnmessage";
            $inittxnstatus = "inittxnstatus";
            $status = "status";
            $code = $throwable->messageCode;
            $userMessage = $throwable->clearMessage;
            $data = $throwable->payResultDto;
            $data->$message = $userMessage;
            $data->$inittxnstatus = $code;
            $data->$status = 'FAILED';
            $displayMessage = sprintf("%s::%s",$code,$userMessage);
            $payMoneyResultDto =  new PayMoneyResultDto($data,$displayMessage);
            $callBackDto =  new MoneyCallbackDto($data->payToken,"FAILED",MoneyCallbackDto::FAILED_MESSAGE);

            $this->httpService->callBack($callBackDto,$data->notifUrl);
            $response = new MoneyPayResponse($payMoneyResultDto);
        }else if($throwable instanceof InvalidMoneyCredentialsException){
            $message = $throwable->getMessage();
            if ($throwable->clearMessage){
                $message = sprintf("%s %s",$throwable->clearMessage,$throwable->getMessage());
            }
            $exceptionEvent->allowCustomResponseCode();

            $tokenErrorDto = new TokenExceptionDto(
                error_description: $message,
                error: "invalid_client"
            );

            $response = new TokenResponse($tokenErrorDto);
            $response->setStatusCode(401);
        }else if ($throwable instanceof MoneyStatusException){
            $response = $exceptionEvent->getResponse();
            if (
                $throwable->messageCode == ExceptionList::PAY_TOKEN_NOT_PROVIDED[ExceptionList::CODE]
            ){
                $response->setStatusCode(400);
            }
            if (
                $throwable->messageCode == ExceptionList::PAY_TOKEN_NOT_FOUND[ExceptionList::CODE]
            ){
                $response->setStatusCode(404);
            }
        }
        else{
            $exceptionEvent->allowCustomResponseCode();
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

            $commandResult->MESSAGE  = $message;

            $errorMessage = sprintf($messageFormat, $code,
                $message,
                $throwable->getFile(),$throwable->getLine());

            $this->logger->critical($errorMessage);
            $commandResult->TXNSTATUS = $code;
            $date = new \DateTime();
            $commandResult->DATE = $date->format('d/m/Y H:i:s');
            $response = new Command($commandResult);
            $response->setStatusCode(200);
        }
        $exceptionEvent->setResponse($response);
    }


}