<?php


namespace AppBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

class AlertsController extends BaseController
{
    /**
     * Gets alert count
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function alertCountAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Gets an alert overview
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function alertOverviewAction(Request $request)
    {
        return $this->success();
    }
}