<?php


namespace AppBundle\Controller;


use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GroupActionController
 *
 * @package AppBundle\Controller
 */
class GroupActionController extends BaseController
{
    /**
     * Join group
     *
     * @Route("groups/{groupId}/join", requirements={"groupId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Join group",
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
    public function joinAction(Request $request)
    {
        return $this->invalid();
    }

    /**
     * Invite users to group
     *
     * @Route("groups/{groupId}/invite", requirements={"groupId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Invite user to group",
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
    public function inviteAction(Request $request)
    {
        return $this->invalid();
    }

    /**
     * Accept group invitation
     *
     * @Route("groups/{groupId}/accept", requirements={"groupId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Accept group invitation",
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
    public function acceptInviteAction(Request $request)
    {
        return $this->invalid();
    }

    /**
     * Decline group invitation
     *
     * @Route("groups/{groupId}/decline", requirements={"groupId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Decline group invitation",
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
    public function declineInviteAction(Request $request)
    {
        return $this->invalid();
    }
}