<?php


namespace AppBundle\Controller;


use AppBundle\Validator\InterestAdditionValidator;
use AppBundle\Validator\InterestArrayValidator;
use AppBundle\Validator\InterestQueryValidator;
use AppBundle\Validator\InterestValidator;
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
     * Get interest overview
     *
     * This is an open API endpoint, no authentication header is required to access it.
     *
     * @Route("interests")
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get interest overview",
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
     * Create new interest
     *
     * @Route("interests")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Create new interest",
     *  tags={},
     *  section="interests",
     *  requirements={
     *
     *  },
     *  parameters={
     *      {
     *          "name"="name",
     *          "dataType"="string",
     *          "description"="Interest name",
     *          "required"="true"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      401="Returned when not authenticated",
     *      409="Returned when a conflict occurs",
     *      500="Returned when an error occured"
     *  },
     *  authentication=false
     * )
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        try {
            $interestValidator = new InterestValidator($request->request->all());
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        }

        $name = $interestValidator->getValue('name');
        $interestManager = $this->get('manager.interest');
        $interest = $interestManager->createInterest($name);

        return $this->success($interest);
    }

    /**
     * Get user interests
     *
     * @Route("users/{userId}/interests", requirements={"userId" = "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get user interests",
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
        $interestManager = $this->get('manager.interest');
        $interests = $interestManager->getUserInterests($userId);

        return $this->success($interests);
    }

    /**
     * Get user's top interests
     *
     * @Route("users/{userId}/top-interests", requirements={"userId": "\d+"})
     * @Method("GET")
     *
     * @ApiDoc(
     *  description="Get user's top interests",
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
     *  authentication=false
     * )
     *
     * @param Request $request
     * @param string $userId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userTopInterestsAction(Request $request, $userId)
    {
        $userId = (int) $userId;
        $interestManager = $this->get('manager.interest');
        $interests = $interestManager->getUserTopInterests($userId);

        if ($interests) {
            return $this->success($interests);
        }

        return $this->invalid();
    }

    /**
     * Add user interests
     *
     * Interests the user already has, will not be touched. Thus, when adding interests
     * the user already has, those interests will be ignored.
     *
     * @Route("users/{userId}/interests", requirements={"userId" = "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Add user interests",
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
                $userManager->setUserStatus($userId, 'ACTIVE');
                return $this->success();
            }

            return $this->invalid();
        }

        return $this->forbidden();
    }

    /**
     * Add interest to user
     *
     * @Route("users/{userId}/interests", requirements={"userId": "\d+"})
     * @Method({"PUT"})
     *
     * @ApiDoc(
     *  description="Add interest to user",
     *  tags={},
     *  section="users",
     *  requirements={
     *
     *  },
     *  parameters={
     *      {
     *          "name"="name",
     *          "dataType"="string",
     *          "required"="true",
     *          "description"="Interest name"
     *      },
     *      {
     *          "name"="visibility",
     *          "dataType"="string",
     *          "required"="0",
     *          "description"="Interest visibility"
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request, $userId)
    {
        try {
            $interestValidator = new InterestAdditionValidator($request->request->all());
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
        } catch (InvalidOptionsException $e) {
            return $this->invalid($e->getMessage());
        } catch (MissingOptionsException $e) {
            return $this->invalid($e->getMessage());
        }

        $userId = (int) $userId;
        $interestName = strtolower($interestValidator->getValue('name'));
        $visibility = $interestValidator->getValue('visibility');
        $accessManager = $this->get('manager.access');
        $accessToken = $tokenValidator->getValue('accessToken');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $interestManager = $this->get('manager.interest');
            $interest = $interestManager->addInterest($userId, $interestName, $visibility);

            if ($interest) {
                return $this->success();
            }

            return $this->invalid();
        }

        return $this->forbidden();
    }

    /**
     * Share interests with e-mail addresses
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
     *  description="Share interests with e-mail addresses",
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
            $userManager->setUserStatus($userId, 'ACTIVE');

            return $this->success();
        }

        return $this->forbidden();
    }

    /**
     * Delete interest from user's list
     *
     * @Route("interests/{interestId}", requirements={"interestId": "\d+"})
     * @Method({"DELETE"})
     *
     * @ApiDoc(
     *  description="Delete interest from user's list",
     *  tags={},
     *  section="interests",
     *  parameters={
     *
     *  },
     *  requirements={
     *      {
     *          "name"="userId",
     *          "dataType"="int",
     *          "required"=true,
     *          "description"="User identifier"
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
     * @param string $interestId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, $interestId)
    {
        try {
            $userValidator = new UserValidator(array(
                'userId' => $request->headers->get('userId')
            ));
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
        } catch (MissingOptionsException $e) {
            return $this->invalid(array(
                'error' => $e->getMessage()
            ));
        } catch (InvalidOptionsException $e) {
            return $this->invalid(array(
                'error' => $e->getMessage()
            ));
        }

        $userId = (int) $userValidator->getValue('userId');
        $interestId = (int) $interestId;
        $accessToken = $tokenValidator->getValue('accessToken');
        $accessManager = $this->get('manager.access');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $userManager = $this->get('manager.user');
            $interest = $userManager->deleteInterest($userId, $interestId);

            if ($interest) {
                return $this->success();
            }

            return $this->invalid();
        }

        return $this->unauthorized();
    }
}
