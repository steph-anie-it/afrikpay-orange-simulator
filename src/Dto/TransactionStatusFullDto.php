<?php
namespace App\Dto;

use App\Service\UtilService;

class TransactionStatusFullDto
{
    public ?Command $command = null;
    public ?CommandHeaderDto $commandHeaderDto = null;

    /**
     * @throws \ReflectionException
     */
    public function __construct(string $content, array $header=[]){

        $utilService = new UtilService();
        $commandXml = simplexml_load_string($content);
        $command = $utilService->mapObjectXml($commandXml,Command::class);
        $this->command = $command;
        $this->commandHeaderDto = $utilService->mapArray($header,CommandHeaderDto::class);
    }
}