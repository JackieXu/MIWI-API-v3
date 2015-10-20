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
        if (is_null($data)) {
            return new Response($data, 400, $headers);
        }

        return new JsonResponse($data, 400, $headers);
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

        if (is_null($data)) {
            return new Response($data, 401, $headers);
        }

        return new JsonResponse($data, 401, $headers);
    }

    /**
     * Returns a 403 response
     *
     * @param mixed $data
     * @param array $headers
     * @return Response
     */
    public function forbidden($data = null, array $headers = array())
    {
        if (is_null($data)) {
            return new Response($data, 403, $headers);
        }

        return new JsonResponse($data, 403, $headers);
    }

    /**
     * Returns a 404 response
     *
     * @param null $data
     * @param array $headers
     * @return JsonResponse|Response
     */
    public function notFound($data = null, array $headers = array())
    {
        if (is_null($data)) {
            return new Response($data, 404, $headers);
        }

        return new JsonResponse($data, 404, $headers);
    }

    /**
     * Returns a 409 response
     *
     * @param mixed $data
     * @param array $headers
     * @return Response
     */
    public function conflict($data = null, array $headers = array())
    {
        if (is_null($data)) {
            return new Response($data, 409, $headers);
        }

        return new JsonResponse($data, 409, $headers);
    }
}