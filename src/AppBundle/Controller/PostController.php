<?php


namespace AppBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PostController
 *
 * @package AppBundle\Controller
 */
class PostController extends BaseController
{
    /**
     * Get posts
     *
     * @Route("posts")
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get posts",
     *  tags={},
     *  section="posts",
     *  requirements={
     *
     *  },
     *  parameters={
     *
     *  },
     *  statusCodes={
     *
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function overviewAction(Request $request)
    {
        return $this->invalid();
    }

    /**
     * Create new post
     *
     * @Route("posts")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Create new post",
     *  tags={},
     *  section="posts",
     *  requirements={
     *
     *  },
     *  parameters={
     *
     *  },
     *  statusCodes={
     *
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        return $this->invalid();
    }

    /**
     * Edit post
     *
     * @Route("posts/{postId}", requirements={"postId": "\d+"})
     * @Method({"PATCH"})
     *
     * @ApiDoc(
     *  description="Edit post",
     *  tags={},
     *  section="posts",
     *  requirements={
     *
     *  },
     *  parameters={
     *
     *  },
     *  statusCodes={
     *
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        return $this->invalid();
    }

    /**
     * Delete post
     *
     * @Route("posts/{postId}", requirements={"postId": "\d+"})
     * @Method({"DELETE"})
     *
     * @ApiDoc(
     *  description="Delete post",
     *  tags={},
     *  section="posts",
     *  requirements={
     *
     *  },
     *  parameters={
     *
     *  },
     *  statusCodes={
     *
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request)
    {
        return $this->invalid();
    }
}