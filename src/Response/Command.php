<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Command extends Response
{
    public function __construct(mixed $content, int $status = 200, array $headers = [])
    {
        $encoder      = [new XmlEncoder()];
        $normalizer   = [new ObjectNormalizer()];
        $serializer   = new Serializer($normalizer, $encoder);
        $rootNames = explode("\\",get_class($this));
        $rootName = strtoupper($rootNames[count($rootNames)-1]);
        $content =
            $serializer->serialize(
                $content,
                XmlEncoder::FORMAT,
                [
                    XmlEncoder::ROOT_NODE_NAME =>$rootName,
                    XmlEncoder::ENCODING => 'UTF-8',
                ]
            );
        parent::__construct($content, $status, array_merge($headers, [
            'Content-Type' => 'text/xml',
        ]));
    }
}