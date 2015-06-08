<?php

namespace AppBundle\Controller;

use AppBundle\Validator\LimitValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TimelineController
 *
 * Handles all requests for every type of timeline.
 *
 * @package AppBundle\Controller
 */
class TimelineController extends BaseController
{
    /**
     * Gets interest timeline
     *
     * @Route("/users/{userId}/interests/{interestId}/timeline/", requirements={"userId": "\d+", "interestId": "\d+"})
     * @Method({"GET", "OPTIONS"})
     *
     * @param Request $request
     * @param int $userId
     * @param int $interestId
     * @return Response
     */
    public function interestTimelineAction(Request $request, $userId, $interestId)
    {
        $userId = (int)$userId;
        $interestId = (int)$interestId;
        $options = new LimitValidator($request->query->all());

        $timelineManager = $this->get('manager.timeline');

        $results = $timelineManager->getInterestTimeline(
            $userId,
            $interestId,
            $options->getValue('offset'),
            $options->getValue('limit')
        );

        return $this->success($results);
    }
}
