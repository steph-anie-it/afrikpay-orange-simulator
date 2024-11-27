<?php

namespace App\Exception;

class ExceptionList
{
     public const CODE = 'code';
     public const MESSAGE = 'message';
     public  const ACCOUNT_NOT_FOUND = [self::CODE => '0061', self::MESSAGE =>'Account not found'];
     public  const INVALID_SUBSCRIBER_NUMBER = [self::CODE =>  '0062' , self::MESSAGE => 'Invalid subscriber number'];
     public  const INVALID_CHANNEL_NUMBER = [  self::CODE => '0063' , self::MESSAGE => 'Invalid channel number'];

     public  const UNKNOWN_MONEY_NUMBER = [  self::CODE => '0064' , self::MESSAGE => 'Unknown mobile money number'];
     public  const INVALID_PIN_NUMBER = [ self::CODE => '0068', self::MESSAGE => 'Pin number'];
     public  const INVALID_PAY_TOKEN_NUMBER = [ self::CODE => '0069', self::MESSAGE => 'Invalid paytoken'];

     public  const INVALID_ORDER_ID = [ self::CODE => '0070', self::MESSAGE => 'Invalid Order id'];
}