<?php

namespace AppBundle\Controller;

use AppBundle\Validator\LimitValidator;
use AppBundle\Validator\SearchValidator;
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
        $offset = (int) $options->getValue('offset');
        $limit = (int) $options->getValue('limit');
        $accessToken = $tokenValidator->getValue('accessToken');
        $accessManager = $this->get('manager.access');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {

            $timelineManager = $this->get('manager.timeline');
            $results = $timelineManager->getInterestTimeline(
                $userId,
                $interestId,
                $offset,
                $limit
            );

            return $this->success($results);

        }

        return $this->forbidden();
    }

    /**
     * Gets interest timeline search results
     *
     * @Route("items/search")
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Gets search results",
     *  tags={},
     *  section="items",
     *  parameters={
     *      {
     *          "name"="query",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="Search query"
     *      },
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
     * @return Response
     */
    public function searchAction(Request $request)
    {
        try {
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
            $searchValidator = new SearchValidator($request->query->all());
        } catch (\Exception $e) {
            return $this->invalid(array(
                'error' => $e->getMessage()
            ));
        }

        $userId = (int) $searchValidator->getValue('userId');
        $accessToken = $tokenValidator->getValue('accessToken');
        $accessManager = $this->get('manager.access');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $query = $searchValidator->getValue('query');
            $offset = (int) $searchValidator->getValue('offset');
            $limit = (int) $searchValidator->getValue('limit');
            $interestId = (int) $searchValidator->getValue('interestId');

            $items = $this->get('manager.content')->search($query, $userId, $interestId, $offset, $limit);

            return $this->success($items);
        }

        return $this->unauthorized();
    }
}
