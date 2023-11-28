<?php

namespace App\Controller\impl;
use App\Exception\BadAuthorizedException;
use App\Exception\InvalidCounterCodeException;
use App\Model\ErrorCode;
use App\Response\ErrorResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use  \Symfony\Component\HttpKernel\Controller\ErrorController as BaseErrorController;
use Exception;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;



class ErrorController extends AbstractController
{

    public const CODE = "code";

    public const TIMESTAMP = "timestamp";
    public const STATUS = "status";

    public const ERROR = "error";
    public const NOTFOUND = "Not Found";

    public const PATH  = "path";
    public const MESSAGE = "message";
    public const DATA = "data";
    public const SERVICE_UNAVAILABLE="Service temporary unavailable. Please try later.";
    public const RFC_EXTENDED_DATE_FORMAT='Y-m-d\TH:i:s.u\Z';

    public const VALIDATION_ERROR="Validation Error found";
    public const COUNTER_CODE='counterCode';

    public const GRANT_TYPE='grantType';

    public const USERNAME='username';

    public const BILL='bill';
    public const PAYMENT_METHOD='paymentMethod';

    public const PASSWORD='password';

    public const CLIENT_ID='clientId';

    public const BALANCE='balance';

    public const CLIENT_SECRET='clientSecret';

    public const ERROR_MESSAGE = "%s %s";

    public const TEXT_SEPARATOR = ":";

    public function __construct(protected RequestStack $requestStack){

    }


    #[Route('error', name: 'error', methods: ['GET','POST'])]
    public function show(Exception|\TypeError|FlattenException  $exception) :ErrorResponse{

        $codeValue = $exception->getCode();
        $messageValue = $exception->getMessage();

        if($exception->getPrevious()){
            $codeValue = $exception->getPrevious()->getCode();
            $messageValue = $exception->getPrevious()->getMessage();
        }

        $dataValue = null;


        if($exception->getCode() > ErrorCode::CODE503->value){
            $codeValue = ErrorCode::CODE503;
            $messageValue = self::SERVICE_UNAVAILABLE;
        }

        if(in_array($codeValue,[BadAuthorizedException::CODE101,BadAuthorizedException::CODE102,
            BadAuthorizedException::CODE103,BadAuthorizedException::CODE104,
            BadAuthorizedException::CODE105,BadAuthorizedException::CODE106])){

            $expMessage = explode(self::TEXT_SEPARATOR,$messageValue);
            $messageValue = self::VALIDATION_ERROR;
            $codeValue = BadAuthorizedException::CODE400;

            if(sizeof($expMessage) == 2){
                $code = trim($expMessage[0]);
                $message = sprintf(self::ERROR_MESSAGE,$code,trim($expMessage[1]));
                $dataValue = [$code => $message];
            }
        }

        $result = [self::CODE => $codeValue,self::MESSAGE=>$messageValue,self::DATA =>$dataValue];
        if(in_array($codeValue, [BadAuthorizedException::CODE0,ErrorCode::CODE404->value])){
            $date = new \DateTime();
            if($codeValue == BadAuthorizedException::CODE0){
                $messageValue = $exception->getMessage();
                $codeValue = 401;
            }else{
                $codeValue = ErrorCode::CODE404;
                $messageValue = self::NOTFOUND;
            }

            $result = [
                self::TIMESTAMP => $date->format(self::RFC_EXTENDED_DATE_FORMAT),
                self::STATUS => $codeValue,
                self::ERROR => $messageValue,
                self::MESSAGE=>"",
                self::PATH =>$this->requestStack->getCurrentRequest()->getRequestUri()];
        }



        return new ErrorResponse($result);
    }

}