<?php

namespace App\Dto\Result;

class CommandResultDto
{
    public ?string $TYPE=null;
    public ?string $TXNSTATUS=null;
    public ?string  $DATE=null;
    public ?string $EXTREFNUM=null;
    public ?string $TXNID=null;

    public ?string $MESSAGE="";
}