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
}