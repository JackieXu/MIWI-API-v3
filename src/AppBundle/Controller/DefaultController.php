<?php


namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * Handles miscellaneous stuff.
 *
 * @package AppBundle\Controller
 */
class DefaultController extends BaseController
{
    /**
     * Catches all OPTIONS requests
     *
     * @Route("/{any}")
     * @Method({"OPTIONS"})
     *
     * @return Response
     */
    public function catchOptionsAction()
    {
        return $this->success();
    }
}
