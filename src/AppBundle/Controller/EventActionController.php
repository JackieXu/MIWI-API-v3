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
}
