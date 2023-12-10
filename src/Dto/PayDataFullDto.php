<?php
namespace App\Dto;

use App\Service\UtilService;

class PayDataFullDto
{
    public ?Command $command = null;
    public ?CommandHeaderDto $commandHeaderDto = null;
    public function __construct(Command $command, array $header=[]){
        $this->command = $command;
        $utilService = new UtilService();
        $this->commandHeaderDto = $utilService->mapArray($header,CommandHeaderDto::class);
    }
}