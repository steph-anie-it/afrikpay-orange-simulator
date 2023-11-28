<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class NumberGenerateResponse extends JsonResponse
{
    public function __construct(mixed $data = null, int $status = 200, array $headers = [], bool $json = false)
    {
        parent::__construct($data, $status, $headers, $json);
    }
}