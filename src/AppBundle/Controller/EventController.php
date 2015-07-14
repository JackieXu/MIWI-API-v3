<?php


namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class EventController extends BaseController
{
    /**
     * Get event overview for interest
     *
     * @Route("events")
     * @Method({"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function overviewAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Get detailed event data
     *
     * @Route("events/{eventId}", requirements={"eventId": "\d+"})
     * @Method("GET")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Create new event
     *
     * @Route("events")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Edit event
     *
     * @Route("events/{eventId}", requirements={"eventId": "\d+"})
     * @Method({"PATCH"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Deletes event
     *
     * @Route("events/{eventId}", requirements={"eventId": "\d+"})
     * @Method({"DELETE"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request)
    {
        return $this->success();
    }
}
