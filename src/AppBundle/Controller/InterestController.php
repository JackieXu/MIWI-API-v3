<?php


namespace AppBundle\Controller;


use AppBundle\Validator\InterestArrayValidator;
use AppBundle\Validator\InterestQueryValidator;
use AppBundle\Validator\ShareObjectValidator;
use AppBundle\Validator\TokenValidator;
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
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="How many interests to return",
     *
     *      },
     *      {
     *          "name"="offset",
     *          "dataType"="integer",
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
        $options = new InterestQueryValidator($request->query->all());

        $interestManager = $this->get('manager.interest');

        try {
            $interests = $interestManager->getInterests(
                $options->getValue('query'),
                $options->getValue('offset'),
                $options->getValue('limit'),
                $options->getValue('defaultOnly')
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400);
        }

        return new JsonResponse($interests, 200);
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
     * @param string $userId
     * @return JsonResponse
     */
    public function userInterestsAction(Request $request, $userId)
    {
        $userId = (int) $userId;
        $accessManager = $this->get('manager.access');

        try {
            $token = new TokenValidator($request->headers->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        if ($accessManager->hasAccessToUser($token, $userId)) {
            $interestManager = $this->get('manager.interest');
            $interests = $interestManager->getUserInterests($userId);

            return $this->success($interests);
        }

        return $this->unauthorized();
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
        try {
            $token = new TokenValidator($request->headers->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $userId = (int) $userId;
        $accessManager = $this->get('manager.access');

        if ($accessManager->hasAccessToUser($token, $userId)) {
            try {
                $options = new InterestArrayValidator($request->request->all());
            } catch (InvalidOptionsException $e) {
                return $this->invalid();
            } catch (MissingOptionsException $e) {
                return $this->invalid();
            }

            $interestManager = $this->get('manager.interest');
            $interests = $interestManager->addInterests($userId, $options->getValue('interests'));

            return $this->success($interests);
        }

        return $this->unauthorized();
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
        try {
            $token = new TokenValidator($request->headers->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $userId = (int) $userId;
        $accessManager = $this->get('manager.access');

        if ($accessManager->hasAccessToUser($token, $userId)) {
            try {
                $options = new ShareObjectValidator($request->request->all());
            } catch (MissingOptionsException $e) {
                return $this->invalid();
            } catch (InvalidOptionsException $e) {
                return $this->invalid();
            }

            // Manual check for types in array, as this is not supported in the OptionsResolver yet
            $shareObject = $options->getValue('shareObject');
            $shareObjectTypeCorrected = array();

            foreach ($shareObject as $interestId => $emailAddresses) {
                if (!is_numeric($interestId)) {
                    return $this->invalid();
                }
                if (!is_array($emailAddresses)) {
                    return $this->invalid();
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
        }

        return $this->unauthorized();
    }
}
