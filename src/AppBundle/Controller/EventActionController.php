<?php


namespace AppBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

class EventActionController extends BaseController
{
    /**
     * Attend event
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function attendAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Don't attend event
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function notAttendAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Invite users to event
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function inviteAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Accept event invitation
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function acceptInviteAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Decline event invitation
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function declineInviteAction(Request $request)
    {
        return $this->success();
    }
}
