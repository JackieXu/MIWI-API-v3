<?php


namespace AppBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ItemController
 *
 * @package AppBundle\Controller
 */
class ItemController extends BaseController
{
    /**
     * Get items
     *
     * @Route("items")
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get items",
     *  tags={},
     *  section="items",
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
     * Create new item
     *
     * @Route("items")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Create new item",
     *  tags={},
     *  section="items",
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
     * Edit item
     *
     * @Route("items/{itemId}", requirements={"itemId": "\d+"})
     * @Method({"PATCH"})
     *
     * @ApiDoc(
     *  description="Edit item",
     *  tags={},
     *  section="items",
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
     * Delete item
     *
     * @Route("items/{itemId}", requirements={"itemId": "\d+"})
     * @Method({"DELETE"})
     *
     * @ApiDoc(
     *  description="Delete item",
     *  tags={},
     *  section="items",
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