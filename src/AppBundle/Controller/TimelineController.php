<?php

namespace AppBundle\Controller;

use AppBundle\Validator\LimitValidator;
use AppBundle\Validator\TimelineValidator;
use AppBundle\Validator\TokenValidator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

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
     * @Route("interests/{interestId}/timeline", requirements={"interestId": "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Gets an interest timeline",
     *  tags={},
     *  section="interests",
     *  parameters={
     *      {
     *          "name"="limit",
     *          "dataType"="int",
     *          "required"=false,
     *          "description"="How many items to return",
     *
     *      },
     *      {
     *          "name"="offset",
     *          "dataType"="int",
     *          "required"=false,
     *          "description"="Number of items to skip"
     *      }
     *  },
     *  requirements={
     *      {
     *          "name"="userId",
     *          "dataType"="int",
     *          "required"=true,
     *          "requirement"="\d+",
     *          "description"="User identifier"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when succesful",
     *      500="Returned when an error occured"
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @param int $interestId
     * @return Response
     */
    public function interestTimelineAction(Request $request, $interestId)
    {
        try {
            $options = new TimelineValidator($request->query->all());
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        }

        try {
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
        } catch (InvalidOptionsException $e) {
            return $this->unauthorized();
        }

        $userId = (int) $options->getValue('userId');
        $interestId = (int) $interestId;
        $accessToken = $tokenValidator->getValue('accessToken');
        $accessManager = $this->get('manager.access');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {

            $timelineManager = $this->get('manager.timeline');
            $results = $timelineManager->getInterestTimeline(
                $userId,
                $interestId,
                $options->getValue('offset'),
                $options->getValue('limit')
            );

            return $this->success($results);

        }

        return $this->forbidden();
    }
}
