<?php


namespace AppBundle\Controller;


use AppBundle\Security\Exception\UserExistsException;
use AppBundle\Validator\GoogleValidator;
use AppBundle\Validator\LoginValidator;
use AppBundle\Validator\RegistrationValidator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * Class AccessController
 *
 * Handles all access going into the application.
 *
 * @package AppBundle\Controller
 */
class AccessController extends BaseController
{
    /**
     * Logs in user
     *
     * Handles the authentication process for users registered via the standard MIWI channels.
     *
     * @Route("/auth")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Logs in user",
     *  tags={},
     *  section="authentication",
     *  parameters={
     *      {
     *          "name"="email",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="User e-mail address"
     *      },
     *      {
     *          "name"="password",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="User password"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      401="Returned when credentials are invalid"
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request)
    {
        try {
            $options = new LoginValidator($request->request->all());
        } catch (InvalidOptionsException $e) {
            return $this->unauthorized();
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        }

        $accessManager = $this->get('manager.access');
        $userToken = $accessManager->login(
            $options->getValue('username'),
            $options->getValue('password')
        );

        return new JsonResponse(array(
            'accessToken' => $userToken
        ));
    }

    /**
     * Logs in user using Google token
     *
     * A Google OAuth access token should be supplied via a header (i.e. `access_token`) to authenticate.
     * The system will attempt to login with the credentials acquired via the Google access token. If no
     * user is found in the system, a new user will be created.
     *
     * @Route("auth/google")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Logs in user using Google token.",
     *  tags={},
     *  section="authentication",
     *  statusCodes={
     *      200="Returned when successful",
     *      401="Returned when credentials are invalid"
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function loginWithGoogleAction(Request $request)
    {
        $token = $request->headers->get('accessToken');

        try {
            $options = new GoogleValidator(array(
                'googleAccessToken' => $token
            ));
        } catch (InvalidOptionsException $e) {
            return $this->unauthorized();
        }

        $accessManager = $this->get('manager.access');
        $userToken = $accessManager->loginWithGoogle($options->getValue('googleAccessToken'));

        return new JsonResponse(array(
            'accessToken' => $userToken
        ));
    }

    /**
     * Registers new user
     *
     * @Route("/users")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Registers new user",
     *  tags={},
     *  section="users",
     *  parameters={
     *      {
     *          "name"="email",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="User e-mail address (e.g. `jackie@miwi.com`)"
     *      },
     *      {
     *          "name"="password",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="User password (e.g. `d#412SxY`)"
     *      },
     *      {
     *          "name"="firstName",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="User first name (e.g. `Jackie`)"
     *      },
     *      {
     *          "name"="lastName",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="User last name (e.g. `Xu`)"
     *      },
     *      {
     *          "name"="birthdate",
     *          "dataType"="integer",
     *          "required"=true,
     *          "description"="User birth date in seconds past since Unix Epoch (e.g. `341498095`)"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      401="Returned when credentials are invalid"
     *  }
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function registerAction(Request $request)
    {
        try {
            $options = new RegistrationValidator($request->request->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid($e->getMessage());
        }

        $accessManager = $this->get('manager.access');

        try {
            list($userId, $accessToken, $status) = $accessManager->register(
                $options->getValue('email'),
                $options->getValue('password'),
                $options->getValue('firstName'),
                $options->getValue('lastName'),
                $options->getValue('birthdate'),
                null
            );
        } catch (UserExistsException $e) {
            return $this->invalid(array(
                $e->getMessage()
            ));
        }

        if ($userId) {
            return $this->success(array(
                'id' => $userId,
                'accessToken' => $accessToken,
                'status' => $status
            ));
        }

        return $this->unauthorized(array(
            'error' => 'E-mail already used'
        ));
    }
}
