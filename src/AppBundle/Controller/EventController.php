<?php


namespace AppBundle\Controller;


use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class EventController extends BaseController
{
    /**
     * Get event overview
     *
     * @Route("events")
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get event overview",
     *  tags={},
     *  section="events",
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
        return $this->success();
    }

    /**
     * Get detailed event data
     *
     * @Route("events/{eventId}", requirements={"eventId": "\d+"})
     * @Method("GET")
     *
     * @ApiDoc(
     *  description="Get detailed event data",
     *  tags={},
     *  section="events",
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
     * @ApiDoc(
     *  description="Create new event",
     *  tags={},
     *  section="events",
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
        return $this->success();
    }

    /**
     * Edit event
     *
     * @Route("events/{eventId}", requirements={"eventId": "\d+"})
     * @Method({"PATCH"})
     *
     * @ApiDoc(
     *  description="Edit event",
     *  tags={},
     *  section="events",
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
    public function editAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Delete event
     *
     * @Route("events/{eventId}", requirements={"eventId": "\d+"})
     * @Method({"DELETE"})
     *
     * @ApiDoc(
     *  description="Delete event",
     *  tags={},
     *  section="events",
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
    public function deleteAction(Request $request)
    {
        return $this->success();
    }
}
