<?php

namespace App\Dto;

class PayMoneyDataResultDto
{
    public function __construct(
        public ?string $createtime = null,
        public ?float $amount = null,
        public ?string $channelUserMsisdn = null,
        public ?string $inittxnmessage = null,
        public ?string $confirmtxnmessage = null,
        public ?string $confirmtxnstatus = null,
        public ?string $subscriberMsisdn = null,
        public ?string $txnmode = null,
        public ?string $notifUrl = null,
        public ?string $inittxnstatus = null,
        public ?string $payToken = null,
        public ?string $txnid = null,
        public ?string $status = null
    ){
    }
}