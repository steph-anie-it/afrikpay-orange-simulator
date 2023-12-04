<?php

namespace App\Response;


use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class PayAirtimeResponse extends Command
{

    public function __construct(mixed $data = null, int $status = 200, array $headers = [], bool $json = false)
    {
        $encoder      = [new XmlEncoder()];
        $normalizer   = [new ObjectNormalizer()];
        $serializer   = new Serializer($normalizer, $encoder);
//        dd($data);
        $content = $serializer->serialize($data,'xml');
        parent::__construct($content, $status, $headers);
    }
}