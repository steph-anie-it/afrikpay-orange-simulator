<?php

namespace App\Listener;

use DateInterval;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class JwTCreateListener
{
    /**
     * Class JWTCreatedListener
     */

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
//    public function onJWTEncoded(JWTEncodedEvent $event): void
//    {
//        $payload = $event->getData();
//        $expiration = new \DateTime();
//        $tokenDuration = $_ENV['TOKEN_DURATION'];
//        $expiration->add(DateInterval::createFromDateString(sprintf("%s %s",$tokenDuration,'seconds')));
//
//        $payload['exp'] = $expiration->getTimestamp();
//        $event->setData($payload);
//    }
//
//    public static function getSubscribedEvents()
//    {
//        return [
//            Events::JWT_ENCODED => [
//                ['onJWTEncoded', 0],
//            ]
//        ];
//    }
}