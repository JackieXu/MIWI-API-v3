<?php


namespace AppBundle\Event\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class CORSListener
 *
 * @package AppBundle\Event\Listener
 */
class CORSListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $method = $request->getMethod();

        if ($method === 'OPTIONS') {
            $response = new JsonResponse();
            $event->setResponse($response);
        }

        if ($method === 'POST' && $request->headers->get('content-type') === 'application/json') {
            $data = json_decode($request->getContent(), true);
            if ($data) {
                $request->initialize(
                    $request->query->all(),
                    $data,
                    $request->attributes->all(),
                    $request->cookies->all(),
                    $request->files->all(),
                    $request->server->all()
                );
            }
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('Access-Control-Allow-Headers', 'origin, content-type, accept');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
    }
}