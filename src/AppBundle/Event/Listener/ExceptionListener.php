<?php


namespace AppBundle\Event\Listener;

use AppBundle\Security\Exception\InvalidLimitException;
use AppBundle\Security\Exception\InvalidOffsetException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\Validator\Exception\InvalidOptionsException;

/**
 * Class ExceptionListener
 *
 * @package AppBundle\Event\Listener
 */
class ExceptionListener
{
    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $exception = $event->getException();
        $response = new Response();

        if ($exception instanceof MissingOptionsException) {
            $response->setStatusCode(RESPONSE::HTTP_BAD_REQUEST);
            $response->setContent('{"error": "Missing required options"}');
        } elseif ($exception instanceof InvalidOptionsException) {
            $response->setStatusCode(RESPONSE::HTTP_BAD_REQUEST);
            $response->setContent('{"error": "Invalid options"}');
        } elseif ($exception instanceof InvalidLimitException) {
            $response->setStatusCode(RESPONSE::HTTP_BAD_REQUEST);
            $response->setContent('{"error": "Invalid limit"}');
        } elseif ($exception instanceof InvalidOffsetException) {
            $response->setStatusCode(RESPONSE::HTTP_BAD_REQUEST);
            $response->setContent('{"error": "Invalid offset"}');
        } else {
            $response->setStatusCode(RESPONSE::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }
}
