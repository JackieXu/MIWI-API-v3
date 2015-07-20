<?php


namespace AppBundle\Controller;


use AppBundle\Validator\ProfileValidator;
use AppBundle\Validator\TokenValidator;
use AppBundle\Validator\UserValidator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class ProfileController extends BaseController
{
    /**
     * Get user profile
     *
     * The `extended` attribute can be set to a value of `1` to get a more extensive profile, which includes:
     *
     *  - Location
     *  - Follower count
     *  - Following count
     *
     * Without the `extended` attribute, the simple view will be returned, which is limited to the following:
     *
     *  - Name
     *  - Image
     *
     * @Route("/users/{userId}/profile", requirements={"userId": "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get user profile",
     *  tags={},
     *  section="users",
     *  requirements={
     *      {
     *          "name"="extended",
     *          "dataType"="int",
     *          "requirement"="\d+",
     *          "description"="Set to 1 for extended profile"
     *      }
     *  },
     *  parameters={
     *
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when parameters are incorrect",
     *      401="Returned when not authenticated",
     *      403="Returned when not authorized",
     *      500="Returned when error occured"
     *  },
     *  authentication=false
     * )
     *
     * @param Request $request
     * @param string $userId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profileAction(Request $request, $userId)
    {
        try {
            $profileValidator = new ProfileValidator($request->query->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $userId = (int) $userId;
        $wantsExtendedProfile = $profileValidator->getValue('extended') === '1';

        $userManager = $this->get('manager.user');
        $profile = $userManager->getProfile($userId, $wantsExtendedProfile);

        if ($profile) {
            return $this->success($profile);
        }

        return $this->invalid();
    }

    /**
     * Get user snapshot
     *
     * A user snapshot call is a combination of asking for a user's interests, alerts, simple profile data and
     * the global timeline.
     *
     * This can be used to simplify API usage, by calling a single endpoint.
     *
     * @Route("/users/{userId}/snapshot", requirements={"userId": "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get user snapshot",
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function snapshotAction(Request $request)
    {
        try {
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
            $userValidator = new UserValidator($request->query->all());
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        }


        return $this->success();
    }
}