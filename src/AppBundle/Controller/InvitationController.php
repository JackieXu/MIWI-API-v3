<?php


namespace AppBundle\Controller;


use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class InvitationController
 *
 * @package AppBundle\Controller
 */
class InvitationController extends BaseController
{
    /**
     * Get invitation overview
     *
     * @Route("invitations")
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get invitation overview",
     *  tags={"unusable"},
     *  section="invitations",
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
    public function overviewAction(Request $request)
    {
        return $this->invalid();
    }

    /**
     * Create new invitation
     *
     * @Route("invitations")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Create new invitation",
     *  tags={},
     *  section="invitations",
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
    public function createAction(Request $request)
    {
        return $this->invalid();
    }

    /**
     * Accept invitation
     *
     * @Route("invitations/{invitationId}/accept", requirements={"invitationId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Accept invitation",
     *  tags={},
     *  section="invitations",
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
    public function acceptAction(Request $request)
    {
        return $this->invalid();
    }

    /**
     * Decline invitation
     *
     * @Route("invitations/{invitationId}/decline", requirements={"invitationId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Decline invitation",
     *  tags={},
     *  section="invitations",
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
    public function declineAction(Request $request)
    {
        return $this->invalid();
    }
}