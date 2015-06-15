<?php


namespace AppBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * Class TimelineActionsController
 *
 * @package AppBundle\Controller
 */
class TimelineActionsController extends BaseController
{
    /**
     * Upvotes an item
     *
     * @Route("items/{itemId}/upvote")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function upvoteAction(Request $request)
    {

    }

    /**
     * Downvotes an item
     *
     * @Route("items/{itemId}/downvote")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function downvoteAction(Request $request)
    {

    }

    /**
     * Comments on an item
     *
     * @param Request $request
     */
    public function commentAction(Request $request)
    {

    }
}
