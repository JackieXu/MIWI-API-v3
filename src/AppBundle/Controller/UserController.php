<?php


namespace AppBundle\Controller;

use AppBundle\Validator\DeviceValidator;
use AppBundle\Validator\FilterValidator;
use AppBundle\Validator\QueryValidator;
use AppBundle\Validator\TokenValidator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * Class UserController
 *
 * @package AppBundle\Controller
 */
class UserController extends BaseController
{
    /**
     * Get user's posts
     *
     * @Route("users/{userId}/posts",requirements={"userId": "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get user's posts",
     *  tags={},
     *  section="users",
     *  requirements={
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
     *      },
     *      {
     *          "name"="interestId",
     *          "dataType"="int",
     *          "required"=false,
     *          "description"="Interest identifier to filter on"
     *      },
     *      {
     *          "name"="query",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="Search query"
     *      }
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
     *  authentication=false
     * )
     *
     * @param Request $request
     * @param string $userId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postsAction(Request $request, $userId)
    {
        try {
            $filterValidator = new FilterValidator($request->query->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $limit = (int) $filterValidator->getValue('limit');
        $offset = (int) $filterValidator->getValue('offset');
        $interestId = (int) $filterValidator->getValue('interestId');
        $query = $filterValidator->getValue('query');
        $userId = (int) $userId;

        $userManager = $this->get('manager.user');
        $posts = $userManager->getUserPosts($userId, $limit, $offset, $interestId,$query);

        return $this->success($posts);
    }

    /**
     * Get user's groups
     *
     * @Route("users/{userId}/groups",requirements={"userId": "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get user's groups",
     *  tags={},
     *  section="users",
     *  requirements={
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
     *      },
     *      {
     *          "name"="interestId",
     *          "dataType"="int",
     *          "required"=true,
     *          "description"="Interest identifier to filter on"
     *      },
     *      {
     *          "name"="query",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="Search query"
     *      }
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
     *  authentication=false
     * )
     *
     * @param Request $request
     * @param string $userId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupsAction(Request $request, $userId)
    {
        try {
            $filterValidator = new FilterValidator($request->query->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $limit = (int) $filterValidator->getValue('limit');
        $offset = (int) $filterValidator->getValue('offset');
        $interestId = (int) $filterValidator->getValue('interestId');
        $query = $filterValidator->getValue('query');
        $userId = (int) $userId;

        $userManager = $this->get('manager.user');
        $groups = $userManager->getUserGroups($userId, $limit, $offset, $interestId,$query);

        return $this->success($groups);
    }

    /**
     * Get user's events
     *
     * @Route("users/{userId}/events", requirements={"userId": "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get user's events",
     *  tags={},
     *  section="users",
     *  requirements={
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
     *      },
     *      {
     *          "name"="interestId",
     *          "dataType"="int",
     *          "required"=true,
     *          "description"="Interest identifier to filter on"
     *      },
     *      {
     *          "name"="query",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="Search query"
     *      }
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
     *  authentication=false
     * )
     *
     * @param Request $request
     * @param string $userId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function eventsAction(Request $request, $userId)
    {
        try {
            $filterValidator = new FilterValidator($request->query->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $limit = (int) $filterValidator->getValue('limit');
        $offset = (int) $filterValidator->getValue('offset');
        $interestId = (int) $filterValidator->getValue('interestId');
        $query = $filterValidator->getValue('query');
        $userId = (int) $userId;

        $userManager = $this->get('manager.user');
        $groups = $userManager->getUserEvents($userId, $limit, $offset, $interestId,$query);

        return $this->success($groups);
    }

    /**
     * Add a user to follow
     *
     * Calling this method again with the same user and followId will remove that
     * user from the following list
     *
     * @Route("users/{userId}/following", requirements={"userId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Add a user to follow",
     *  tags={},
     *  section="users",
     *  requirements={
     *      {
     *          "name"="followId",
     *          "dataType"="int",
     *          "required"=true,
     *          "description"="User identifier of person to follow",
     *      }
     *  },
     *  parameters={
     *
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when parameters are invalid or missing",
     *      401="Returned when not authenticated",
     *      403="Returned when not authorized",
     *      500="Returned when an error occured"
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @param $userId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function followAction(Request $request, $userId)
    {
        try {
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
        } catch (InvalidOptionsException $e) {
            return $this->invalid(array(
                'error' => $e->getMessage()
            ));
        } catch (MissingOptionsException $e) {
            return $this->invalid(array(
                'error' => $e->getMessage()
            ));
        }

        $followId = (int) $request->request->get('followId');
        $userId = (int) $userId;
        $accessToken = $tokenValidator->getValue('accessToken');
        $userManager = $this->get('manager.user');
        $accessManager = $this->get('manager.access');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $followUser = $userManager->followUser($followId, $userId);

            if ($followUser) {
                return $this->success();
            }

            return $this->invalid(array(
                'error' => 'Invalid user to follow'
            ));
        }

        return $this->unauthorized();
    }

    /**
     * Get user's followers
     *
     * @Route("users/{userId}/followers", requirements={"userId": "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get user's followers",
     *  tags={},
     *  section="users",
     *  requirements={
     *      {
     *          "name"="limit",
     *          "dataType"="int",
     *          "required"=false,
     *          "description"="How many items to return",
     *      },
     *      {
     *          "name"="offset",
     *          "dataType"="int",
     *          "required"=false,
     *          "description"="Number of items to skip"
     *      },
     *      {
     *          "name"="query",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="Search query"
     *      }
     *  },
     *  parameters={
     *
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when parameters are invalid or missing",
     *      401="Returned when not authenticated",
     *      403="Returned when not authorized",
     *      500="Returned when an error occured"
     *  },
     *  authentication=false
     * )
     *
     * @param Request $request
     * @param string $userId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function followersAction(Request $request, $userId)
    {
        try {
            $queryValidator = new QueryValidator($request->query->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid($e->getMessage());
        } catch (InvalidOptionsException $e) {
            return $this->invalid($e->getMessage());
        }

        $userId = (int) $userId;
        $offset = (int) $queryValidator->getValue('offset');
        $limit = (int) $queryValidator->getValue('limit');
        $query = $queryValidator->getValue('query');

        $userManager = $this->get('manager.user');

        $people = $userManager->getUserFollowers($userId, $offset, $limit, $query);

        return $this->success($people);
    }

    /**
     * Get people the user is following
     *
     * @Route("users/{userId}/following", requirements={"userId": "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get people the user is following",
     *  tags={},
     *  section="users",
     *  requirements={
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
     *      },
     *      {
     *          "name"="query",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="Search query"
     *      }
     *  },
     *  parameters={
     *
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when parameters are invalid or missing",
     *      401="Returned when not authenticated",
     *      403="Returned when not authorized",
     *      500="Returned when an error occured"
     *  },
     *  authentication=false
     * )
     *
     * @param Request $request
     * @param string $userId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function followingAction(Request $request, $userId)
    {
        try {
            $queryValidator = new QueryValidator($request->query->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid($e->getMessage());
        } catch (InvalidOptionsException $e) {
            return $this->invalid($e->getMessage());
        }

        $userId = (int) $userId;
        $offset = (int) $queryValidator->getValue('offset');
        $limit = (int) $queryValidator->getValue('limit');
        $query = $queryValidator->getValue('query');

        $userManager = $this->get('manager.user');

        $people = $userManager->getUserFollowing($userId, $offset, $limit, $query);

        return $this->success($people);
    }

    /**
     * Add user device
     *
     * @Route("users/{userId}/devices", requirements={"userId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Add user device",
     *  tags={},
     *  section="users",
     *  requirements={
     *      {
     *          "name"="deviceId",
     *          "dataType"="string",
     *          "requirement"="",
     *          "description"="Device identifiction string"
     *      },
     *      {
     *          "name"="deviceType",
     *          "dataType"="string",
     *          "requirement"="",
     *          "description"="Device type"
     *      },
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addDeviceAction(Request $request, $userId)
    {
        try {
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
            $deviceValidator = new DeviceValidator($request->request->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid(array(
                'error' => $e->getMessage()
            ));
        } catch (InvalidOptionsException $e) {
            return $this->invalid(array(
                'error' => $e->getMessage()
            ));
        }

        $accessManager = $this->get('manager.access');
        $accessToken = $tokenValidator->getValue('accessToken');
        $userId = (int) $userId;

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $userManager = $this->get('manager.user');
            $deviceType = $deviceValidator->getValue('deviceType');
            $deviceId = $deviceValidator->getValue('deviceId');

            $device = $userManager->addDevice($userId, $deviceId, $deviceType);

            if ($device) {
                return $this->success();
            }

            return $this->invalid();
        }

        return $this->unauthorized();
    }

    /**
     * Deactivate user
     *
     * @Route("users/{userId}/deactivate", requirements={"userId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Deactivate user",
     *  tags={},
     *  section="users",
     *  requirements={
     *  },
     *  parameters={
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
    public function deactivateAction(Request $request, $userId)
    {
        try {
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

        $userId = (int) $userId;
        $accessToken = $tokenValidator->getValue('accessToken');
        $accessManager = $this->get('manager.access');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $userManager = $this->get('manager.user');
            $status = $userManager->setUserStatus($userId, 'INACTIVE');

            if ($status) {
                return $this->success();
            }

            return $this->invalid();
        }

        return $this->unauthorized();
    }

    /**
     * Reactivate user
     *
     * @Route("users/{userId}/reactivate", requirements={"userId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Reactivate user",
     *  tags={},
     *  section="users",
     *  requirements={
     *  },
     *  parameters={
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
    public function reactivateAction(Request $request, $userId)
    {
        try {
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

        $userId = (int) $userId;
        $accessToken = $tokenValidator->getValue('accessToken');
        $accessManager = $this->get('manager.access');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $userManager = $this->get('manager.user');
            $status = $userManager->setUserStatus($userId, 'ACTIVE');

            if ($status) {
                return $this->success();
            }

            return $this->invalid();
        }

        return $this->unauthorized();
    }

    /**
     * Delete user
     *
     * @Route("users/{userId}/delete", requirements={"userId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Delete user",
     *  tags={},
     *  section="users",
     *  requirements={
     *  },
     *  parameters={
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
    public function deleteAction(Request $request, $userId)
    {
        try {
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

        $userId = (int) $userId;
        $accessToken = $tokenValidator->getValue('accessToken');
        $accessManager = $this->get('manager.access');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $userManager = $this->get('manager.user');
            $status = $userManager->deleteUser($userId);

            if ($status) {
                return $this->success();
            }

            return $this->invalid();
        }

        return $this->unauthorized();
    }
}