<?php

namespace App\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelEvents;

class KernelExceptionSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }



    public function onKernelException(
        \Symfony\Component\HttpKernel\Event\ExceptionEvent $exceptionEvent,
        
    ) {
        $throwable = $exceptionEvent->getThrowable();
        
        $jsonResponse = new JsonResponse();
        $jsonResponse->setStatusCode($throwable->getStatusCode());
        $data = [
            'status' => $throwable->getStatusCode(),
            'message' => $throwable->getMessage()
        ];
        
        $jsonResponse->setContent(json_encode($data));
        $exceptionEvent->setResponse($jsonResponse);
    }
}