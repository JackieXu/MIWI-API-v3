<?php


namespace AppBundle\Controller;


use AppBundle\Security\Exception\UserExistsException;
use AppBundle\Validator\EmailValidator;
use AppBundle\Validator\FacebookValidator;
use AppBundle\Validator\GoogleValidator;
use AppBundle\Validator\LoginValidator;
use AppBundle\Validator\PasswordTokenValidator;
use AppBundle\Validator\RegistrationValidator;
use AppBundle\Validator\TokenValidator;
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
     * Log in user
     *
     * Handles the authentication process for users registered via the standard MIWI channels.
     *
     * @Route("/auth")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Log in user",
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
            $options->getValue('email'),
            $options->getValue('password')
        );

        if (!$userToken) {
            return $this->unauthorized();
        }

        // Add zero alerts
        $userToken['alerts'] = 0;

        return $this->success($userToken);
    }

    /**
     * Log in user using Google token
     *
     * A Google OAuth access token should be supplied via a header (i.e. `access_token`) to authenticate.
     * The system will attempt to login with the credentials acquired via the Google access token. If no
     * user is found in the system, a new user will be created.
     *
     * @Route("auth/google")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Log in user using Google token.",
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
        $token = $request->request->get('accessToken');

        try {
            $options = new GoogleValidator(array(
                'googleAccessToken' => $token
            ));
        } catch (InvalidOptionsException $e) {
            return $this->unauthorized(array(
                'error' => $e->getMessage()
            ));
        } catch (MissingOptionsException $e) {
            return $this->unauthorized(array(
                'error' => $e->getMessage()
            ));
        }

        $accessManager = $this->get('manager.access');
        $userToken = $accessManager->loginWithGoogle($options->getValue('googleAccessToken'));

        if ($userToken) {
            return $this->success($userToken);
        }

        return $this->invalid();
    }

    /**
     * Log in user using Facebook token
     *
     * A Facebook OAuth access token should be supplied via a header (i.e. `access_token`) to authenticate.
     * The system will attempt to login with the credentials acquired via the Facebook access token. If no
     * user is found in the system, a new user will be created.
     *
     * @Route("auth/facebook")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Log in user using Facebook token.",
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
    public function loginWithFacebookAction(Request $request)
    {
        $token = $request->request->get('accessToken');

        try {
            $options = new FacebookValidator(array(
                'facebookAccessToken' => $token
            ));
        } catch (InvalidOptionsException $e) {
            return $this->unauthorized();
        }

        $accessManager = $this->get('manager.access');
        $userToken = $accessManager->loginWithFacebook($options->getValue('facebookAccessToken'));

        if ($userToken) {
            return $this->success($userToken);
        }

        return $this->invalid();
    }

    /**
     * Register new user
     *
     * @Route("/users")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Register new user",
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
     *          "dataType"="int",
     *          "required"=true,
     *          "description"="User birth date in seconds past since Unix Epoch (e.g. `341498095`)"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when parameters are missing or invalid",
     *      409="Returned when credentials are already used"
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
            $data = $accessManager->register(
                $options->getValue('email'),
                $options->getValue('password'),
                $options->getValue('firstName'),
                $options->getValue('lastName'),
                $options->getValue('birthdate'),
                null
            );
            $userId = $data['id'];
            $accessToken = $data['accessToken'];
            $status = $data['status'];
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

        return $this->conflict();
    }

    /**
     * Request password token
     *
     * @Route("/auth/password-token")
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Request password recovery token",
     *  tags={},
     *  section="authentication",
     *  requirements={
     *
     *  },
     *  parameters={
     *      {
     *          "name"="email",
     *          "dataType"="string",
     *          "required"="true",
     *          "description"="User's e-mail address"
     *      }
     *  },
     *  authentication=false,
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when parameters are missing or invalid"
     *  }
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function requestPasswordTokenAction(Request $request)
    {
        try {
            $options = new EmailValidator($request->query->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $accessManager = $this->get('manager.access');

        // Replace spaces with +
        $email = str_replace(' ', '+', $options->getValue('email'));
        $token = $accessManager->requestPasswordToken($email);

        if ($token) {
            return $this->success();
        }

        return $this->invalid();
    }

    /**
     * Reset password
     *
     * @Route("/auth/change-password")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Change user password using token",
     *  tags={},
     *  section="authentication",
     *  requirements={
     *
     *  },
     *  parameters={
     *      {
     *          "name"="token",
     *          "dataType"="string",
     *          "required"="true",
     *          "description"="Password recovery token"
     *      },
     *      {
     *          "name"="password",
     *          "dataType"="string",
     *          "required"="true",
     *          "description"="New password"
     *      }
     *  },
     *  authentication=false,
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when parameters are missing or invalid"
     *  }
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function resetPasswordAction(Request $request)
    {
        try {
            $options = new PasswordTokenValidator($request->request->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $accessManager = $this->get('manager.access');

        $isSuccess = $accessManager->changePassword(
            $options->getValue('token'),
            $options->getValue('password')
        );

        if ($isSuccess) {
            return $this->success();
        }

        return $this->unauthorized();
    }
}
