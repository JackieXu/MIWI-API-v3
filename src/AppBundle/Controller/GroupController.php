<?php


namespace AppBundle\Controller;


use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class GroupController extends BaseController
{
    /**
     * Get group overview
     *
     * @Route("groups")
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get group overview",
     *  tags={},
     *  section="groups",
     *  requirements={
     *
     *  },
     *  parameters={
     *
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      401="Returned when not authenticated",
     *      403="Returned when not authorized",
     *      500="Returned when an error occured"
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupOverviewAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Get detailed group data
     *
     * @Route("groups/{groupId}", requirements={"groupId": "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get detailed group data",
     *  tags={},
     *  section="groups",
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
    public function viewGroupAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Create new group
     *
     * @Route("groups")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Create new group",
     *  tags={},
     *  section="groups",
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
    public function createGroupAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Edit group
     *
     * @Route("groups/{groupId}", requirements={"groupId": "\d+"})
     * @Method({"PATCH"})
     *
     * @ApiDoc(
     *  description="Edit group",
     *  tags={},
     *  section="groups",
     *  requirements={
     *      {
     *          "name"="userId",
     *          "dataType"="int",
     *          "requirement"="\d+",
     *          "description"="User identifier"
     *      }
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
    public function editGroupAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Delete group
     *
     * @Route("groups/{groupId}", requirements={"groupId": "\d+"})
     * @Method({"DELETE"})
     *
     * @ApiDoc(
     *  description="Delete group",
     *  tags={},
     *  section="groups",
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
    public function deleteGroupAction(Request $request)
    {
        return $this->success();
    }
}