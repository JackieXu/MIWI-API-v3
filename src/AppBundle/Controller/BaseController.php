<?php


namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BaseController
 *
 * @package AppBundle\Controller
 */
abstract class BaseController extends Controller
{
    /**
     * Returns a success response
     *
     * Depending on `$data`, the system infers which status code to send.
     *
     * @param mixed $data
     * @param array $headers
     * @return Response
     */
    public function success($data = null, array $headers = array()) {
        if (is_null($data)) {
            return new Response($data, 204, $headers);
        }

        return new JsonResponse($data, 200, $headers);
    }

    /**
     * Returns a 400 response
     *
     * @param mixed $data
     * @param array $headers
     * @return Response
     */
    public function invalid($data = null, array $headers = array())
    {
        return new Response($data, 400, $headers);
    }

    /**
     * Returns a 401 response
     *
     * Sets the required WWW-Authenticate header to `OAuth`, indicating the requirement
     * of an authentication header value.
     *
     * @param mixed $data
     * @param array $headers
     * @return Response
     */
    public function unauthorized($data = null, array $headers = array())
    {
        $headers['WWW-Authenticate'] = 'OAuth';

        return new Response($data, 401, $headers);
    }
}