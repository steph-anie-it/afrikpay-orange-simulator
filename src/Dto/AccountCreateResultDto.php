<?php

namespace App\Dto;

class AccountCreateResultDto
{
    public string $username;
    public string $apikey;
    public ?string $subscriptionkey = null;

    public string $msisdn;

    public ?int $selector = null;

    public ?string $extnwcode = null;

    public ?string $pin = null;

    public ?string $msisdn2 = null;

    public ?int $language1 = null;

    public ?int $language2 = null;

    public ?string $type = null;

    public ?string $login = null;

    public ?string $sourcetype = null;

    public ?int $serviceport = null;

    public ?string $requestgatewaytype = null;

    public ?string $requestgatewaycode = null;

}