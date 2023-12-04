<?php

namespace App\Dto;

class PayAirtimeTransactionResultDto
{
    public ?string $transaction_status = null;
    public ?string $api_key;
    public ?string $ext_trans_id;
    public ?string $requesting_account;
    public ?string $receiving_account;
    public ?string $receiving_msisdn;
    public  $completed_on;
    public  $requested_on;

    public ?string  $return_code;
    public ?string  $result_text;

    public ?string $amount;
}