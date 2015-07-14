<?php


namespace AppBundle\Controller;


use AppBundle\Validator\InterestArrayValidator;
use AppBundle\Validator\InterestQueryValidator;
use AppBundle\Validator\ShareObjectValidator;
use AppBundle\Validator\TokenValidator;
use AppBundle\Validator\UserValidator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * Class InterestController
 *
 * Handles all requests pertaining to interests.
 *
 * @package AppBundle\Controller
 */
class InterestController extends BaseController
{
    /**
     * Gets interest overview
     *
     * This is an open API endpoint, no authentication header is required to access it.
     *
     * @Route("interests")
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Gets interest overview",
     *  tags={},
     *  section="interests",
     *  requirements={
     *      {
     *          "name"="limit",
     *          "dataType"="int",
     *          "requirement"="\d+",
     *          "description"="How many interests to return",
     *
     *      },
     *      {
     *          "name"="offset",
     *          "dataType"="int",
     *          "requirement"="\d+",
     *          "description"="Number of interests to skip"
     *      },
     *      {
     *          "name"="query",
     *          "dataType"="string",
     *          "requirement"="N/A",
     *          "description"="Interest search query"
     *      },
     *      {
     *          "name"="defaultOnly",
     *          "dataType"="boolean",
     *          "requirement"="N/A",
     *          "description"="If true, returns only default interests"
     *      },
     *  },
     *  parameters={
     *
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      500="Returned when an error occured"
     *  },
     *  authentication=false
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function overviewAction(Request $request)
    {
        $parameters = $request->query->all();
        if (array_key_exists('defaultOnly', $parameters) && $parameters['defaultOnly'] !== '0' && $parameters['defaultOnly'] !== 'false' && !empty($parameters['defaultOnly'])) {
            $parameters['defaultOnly'] = true;
        } else {
            $parameters['defaultOnly'] = false;
        }

        $options = new InterestQueryValidator($parameters);

        $interestManager = $this->get('manager.interest');

        try {
            $interests = $interestManager->getInterests(
                $options->getValue('query'),
                $options->getValue('offset'),
                $options->getValue('limit'),
                $options->getValue('defaultOnly')
            );
        } catch (\Exception $e) {
            return $this->invalid();
        }

        return $this->success($interests);
    }

    /**
     * Gets user interests
     *
     * @Route("users/{userId}/interests", requirements={"userId" = "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Gets user interests",
     *  tags={},
     *  section="users",
     *  requirements={
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
     * @param string $userId
     * @return JsonResponse
     */
    public function userInterestsAction(Request $request, $userId)
    {
        $userId = (int) $userId;
        $accessToken = $request->headers->get('accessToken', '');
        $accessManager = $this->get('manager.access');

        try {
            $token = new TokenValidator(array(
                'accessToken' => $accessToken
            ));
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        if ($accessManager->hasAccessToUser($token->getValue('accessToken'), $userId)) {
            $interestManager = $this->get('manager.interest');
            $interests = $interestManager->getUserInterests($userId);

            return $this->success($interests);
        }

        return $this->forbidden();
    }

    /**
     * Gets user's top interests
     *
     * @Route("users/{userId}/top-interests", requirements={"userId": "\d+"})
     * @Method("GET")
     *
     * @ApiDoc(
     *  description="Gets user's top interests",
     *  tags={},
     *  section="users",
     *  requirements={
     *
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
     * @param string $userId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userTopInterestsAction(Request $request, $userId)
    {
        try {
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $accessManager = $this->get('manager.access');
        $userId = (int) $userId;
        $accessToken = $tokenValidator->getValue('accessToken');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $interestManager = $this->get('manager.interest');
            $interests = $interestManager->getUserTopInterests($userId);

            if ($interests) {
                return $this->success($interests);
            }

            return $this->invalid();
        }

        return $this->forbidden();
    }

    /**
     * Adds user interests
     *
     * Interests the user already has, will not be touched. Thus, when adding interests
     * the user already has, those interests will be ignored.
     *
     * @Route("users/{userId}/interests", requirements={"userId" = "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Adds user interests",
     *  tags={},
     *  section="users",
     *  parameters={
     *      {
     *          "name"="interestNames",
     *          "dataType"="string[]",
     *          "required"=true,
     *          "description"="Array of interest names"
     *      }
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
     * @param string $userId
     * @return JsonResponse
     */
    public function batchAddAction(Request $request, $userId)
    {
        $userId = (int) $userId;
        $accessToken = $request->headers->get('accessToken', '');
        $accessManager = $this->get('manager.access');

        try {
            $token = new TokenValidator(array(
                'accessToken' => $accessToken
            ));
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }


        if ($accessManager->hasAccessToUser($token->getValue('accessToken'), $userId)) {
            try {
                $options = new InterestArrayValidator($request->request->all());
            } catch (InvalidOptionsException $e) {
                return $this->invalid();
            } catch (MissingOptionsException $e) {
                return $this->invalid();
            }

            $interestManager = $this->get('manager.interest');
            $interests = $interestManager->addInterests($userId, $options->getValue('interestNames'));

            if ($interests) {
                $userManager = $this->get('manager.user');
                $userManager->setUserStatus($userId, 1);
                return $this->success();
            }

            return $this->invalid();
        }

        return $this->forbidden();
    }

    /**
     * Shares interest with e-mail addresses
     *
     * Requires an object containing interests and e-mail addresses the interest
     * should be shared with.
     *
     * E-mail addresses have to be either of the following formats:
     *
     *  - `name@domain.com`
     *  - `display name <name@domain.com>`
     *
     * In the second example, `display name` will be used in the invitation mail.
     *
     * An example object:
     * <pre>
     * {
     *  25: ['kees@miwi.com', 'Jackie Xu &lt;jackie@miwi.com&gt;'],
     *  174: [],
     *  1891: ['kees@miwi.com']
     * }
     * </pre>
     *
     * @Route("users/{userId}/share", requirements={"userId"="\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Shares interest with given e-mail addresses",
     *  tags={},
     *  section="users",
     *  parameters={
     *
     *  },
     *  requirements={
     *      {
     *          "name"="shareObject",
     *          "dataType"="Map<int, string[]>",
     *          "required"=true,
     *          "description"="See example object"
     *      }
     *  },
     *  statusCodes={
     *      204="Returned when successful",
     *      401="Returned when not authenticated",
     *      403="Returned when not authorized",
     *      500="Returned when an error occured"
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @param string $userId
     * @return JsonResponse
     */
    public function batchShareAction(Request $request, $userId)
    {
        $accessToken = $request->headers->get('accessToken', '');
        try {
            $token = new TokenValidator(array(
                'accessToken' => $accessToken
            ));
        } catch (MissingOptionsException $e) {
            return $this->invalid(array(
                'error' => 'Missing token'
            ));
        } catch (InvalidOptionsException $e) {
            return $this->unauthorized();
        }

        $userId = (int) $userId;
        $accessManager = $this->get('manager.access');

        if ($accessManager->hasAccessToUser($token->getValue('accessToken'), $userId)) {
            try {
                $options = new ShareObjectValidator($request->request->all());
            } catch (MissingOptionsException $e) {
                return $this->invalid(array(
                    'error' => 'Missing share object'
                ));
            } catch (InvalidOptionsException $e) {
                return $this->invalid(array(
                    'error' => 'Invalid share object'
                ));
            }

            // Manual check for types in array, as this is not supported in the OptionsResolver yet
            $shareObject = json_decode($options->getValue('shareObject'), true);
            $shareObjectTypeCorrected = array();

            foreach ($shareObject as $interestId => $emailAddresses) {
                if (!is_numeric($interestId)) {
                    return $this->invalid(array(
                        'error' => 'Invalid share object'
                    ));
                }
                if (!is_array($emailAddresses)) {
                    return $this->invalid(array(
                        'error' => 'Invalid share object'
                    ));
                }

                $interestIdTypeCorrected = (int) $interestId;
                $shareObjectTypeCorrected[$interestIdTypeCorrected] = array();

                foreach ($emailAddresses as $emailAddress) {
                    list($rawName, $rawEmail) = explode('<', $emailAddress);

                    // No name supplied, only an e-mail address
                    if (is_null($rawEmail)) {
                        $name = null;
                        $email = trim($rawName);
                    } else {
                        $name = trim($rawName);
                        $email = trim($rawEmail, '>');
                    }

                    $shareObjectTypeCorrected[$interestIdTypeCorrected][] = array($name, $email);
                }
            }

            $interestManager = $this->get('manager.interest');
            $interestManager->shareInterests($shareObjectTypeCorrected);

            $userManager = $this->get('manager.user');
            $userManager->setUserStatus($userId, 2);

            return $this->success();
        }

        return $this->forbidden();
    }

    /**
     * Gets groups associated with interest
     *
     * @Route("interests/{interestId}/groups", requirements={"interestId": "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Gets groups associated with interest",
     *  tags={},
     *  section="groups",
     *  requirements={
     *
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
     * @param string $interestId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function interestGroupsAction(Request $request, $interestId)
    {
        try {
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
            $userValidator = new UserValidator($request->query->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $accessManager = $this->get('manager.access');
        $userId = (int) $userValidator->getValue('userId');
        $interestId = (int) $interestId;
        $accessToken = $tokenValidator->getValue('accessToken');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $groupManager = $this->get('manager.group');
            $groups = $groupManager->getInterestGroups($interestId);

            if ($groups) {
                return $this->success($groups);
            }

            return $this->invalid();
        }

        return $this->forbidden();
    }

    /**
     * Gets events associated with interest
     *
     * @Route("interests/{interestId}/events", requirements={"interestId": "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Gets events associated with interest",
     *  tags={},
     *  section="events",
     *  requirements={
     *
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
     * @param string $interestId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function interestEventsAction(Request $request, $interestId)
    {
        try {
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
            $userValidator = new UserValidator($request->query->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $accessManager = $this->get('manager.access');
        $userId = (int) $userValidator->getValue('userId');
        $interestId = (int) $interestId;
        $accessToken = $tokenValidator->getValue('accessToken');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $eventManager = $this->get('manager.event');
            $events = $eventManager->getInterestEvents($interestId);

            if ($events) {
                return $this->success($events);
            }

            return $this->invalid();
        }

        return $this->forbidden();
    }
}
