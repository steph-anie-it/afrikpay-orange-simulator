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

    public  const INVALID_MONEY_NUMBER = [  self::CODE => '0065' , self::MESSAGE => 'Invalid mobile money number'];
     public  const INVALID_PIN_NUMBER = [ self::CODE => '0068', self::MESSAGE => 'Pin number'];
     public  const INVALID_PAY_TOKEN_NUMBER = [ self::CODE => '0069', self::MESSAGE => 'Invalid paytoken'];
     public  const INVALID_ORDER_ID = [ self::CODE => '0070', self::MESSAGE => 'Invalid Order id'];
     public  const INVALID_PAY_TOKEN_TRANSACTION_NUMBER = [ self::CODE => '0071', self::MESSAGE => 'Invalid transaction paytoken'];
     public  const INVALID_AMOUNT = [ self::CODE => '0072', self::MESSAGE => 'Invalid Amount'];
     public  const NOT_ENOUGH_FUND = [ self::CODE => '0073', self::MESSAGE => 'Not Enough fund'];

     public  const INVALID_TOKEN_CLIENT_ID = [ self::CODE => '0074', self::MESSAGE => 'A valid OAuth client could not be found for client_id'];

    public  const UNKNOW_USER = [ self::CODE => '0075', self::MESSAGE => 'Unknown user '];
    public  const INVALID_WSO2_TOKEN = [ self::CODE => '0076', self::MESSAGE => 'Invalid bearer token '];

    public  const INVALID_XAUTH_TOKEN = [ self::CODE => '0076', self::MESSAGE => 'Invalid xauth token '];
    public  const INVALID_GRANT_TYPE = [ self::CODE => '0077', self::MESSAGE => 'Invalid grant type '];
    public  const INVALID_APIKEY_TYPE = [ self::CODE => '0078', self::MESSAGE => 'Invalid api key type '];
    public  const INVALID_SUBSCRIPTION_KEY_TYPE = [ self::CODE => '0079', self::MESSAGE => 'Invalid subscription key type '];
    public  const INVALID_CREDENTIALS = [ self::CODE => '0080', self::MESSAGE => 'Invalid credentials '];

    public  const BAD_WSO2_TOKEN = [ self::CODE => '0081', self::MESSAGE => 'Bad wso2 token '];
    public  const EXPIRY_JWT_TOKEN = [ self::CODE => '0082', self::MESSAGE => 'Expiry JWT token '];

    public  const INVALID_USER_JWT_TOKEN = [ self::CODE => '0083', self::MESSAGE => 'Invalid User JWT token '];

    public  const INVALID_ACCOUNT_CHANNEL_JWT_TOKEN = [ self::CODE => '0084', self::MESSAGE => 'Invalid  Account Money channel'];
    public  const INVALID_URL = [ self::CODE => '0085', self::MESSAGE => 'Invalid  Url'];
}