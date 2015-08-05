<?php


namespace AppBundle\Controller;


use AppBundle\Validator\FilterValidator;
use AppBundle\Validator\GroupValidator;
use AppBundle\Validator\QueryValidator;
use AppBundle\Validator\TokenValidator;
use AppBundle\Validator\UserValidator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class GroupController extends BaseController
{
    /**
     * Get group overview
     *
     * @Route("groups")
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get group overview",
     *  tags={},
     *  section="groups",
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupOverviewAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Get detailed group data
     *
     * @Route("groups/{groupId}", requirements={"groupId": "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get detailed group data",
     *  tags={},
     *  section="groups",
     *  requirements={
     *      {
     *          "name"="userId",
     *          "dataType"="int",
     *          "requirement"="\d+",
     *          "description"="User identifier"
     *      }
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
     * @param string $groupId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewGroupAction(Request $request, $groupId)
    {
        try {
            $userValidator = new UserValidator($request->query->all());
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $userId = (int) $userValidator->getValue('userId');
        $groupId = (int) $groupId;
        $accessToken = $tokenValidator->getValue('accessToken');
        $groupManager = $this->get('manager.group');
        $accessManager = $this->get('manager.access');


        if ($accessManager->hasAccessToUser($accessToken, $userId)) {

            $group = $groupManager->getGroup($groupId, $userId);

            if ($group) {
                return $this->success($group);
            }

            return $this->createNotFoundException();

        }

        return $this->forbidden();
    }

    /**
     * Create new group
     *
     * @Route("groups")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Create new group",
     *  tags={},
     *  section="groups",
     *  requirements={
     *      {
     *          "name"="title",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="Group name"
     *      },
     *      {
     *          "name"="description",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="Group description"
     *      },
     *      {
     *          "name"="visibility",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="Group visibility setting (either `private` or `public`)"
     *      },
     *      {
     *          "name"="interestId",
     *          "dataType"="int",
     *          "required"=true,
     *          "description"="Interest the group should be linked to"
     *      },
     *      {
     *          "name"="website",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="Group website"
     *      },
     *      {
     *          "name"="userId",
     *          "dataType"="int",
     *          "required"=true,
     *          "description"="User identifier"
     *      }
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
    public function createGroupAction(Request $request)
    {
        try {
            $groupValidator = new GroupValidator($request->request->all());
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
        } catch (MissingOptionsException $e) {
            return $this->invalid($e->getMessage());
        } catch (InvalidOptionsException $e) {
            return $this->invalid($e->getMessage());
        }

        $accessManager = $this->get('manager.access');
        $accessToken = $tokenValidator->getValue('accessToken');
        $userId = (int) $groupValidator->getValue('userId');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {

            $interestId = (int) $groupValidator->getValue('interestId');
            $title = $groupValidator->getValue('title');
            $description = $groupValidator->getValue('description');
            $visibility = $groupValidator->getValue('visibility');
            $website = $groupValidator->getValue('website');
            $groupManager = $this->get('manager.group');

            $group = $groupManager->createGroup($title, $description, $website, $visibility, $interestId, $userId);

            if ($group) {
                return $this->success($group);
            }

            return $this->conflict();
        }

        return $this->forbidden();
    }

    /**
     * Edit group
     *
     * @Route("groups/{groupId}", requirements={"groupId": "\d+"})
     * @Method({"PATCH"})
     *
     * @ApiDoc(
     *  description="Edit group",
     *  tags={},
     *  section="groups",
     *  requirements={
     *      {
     *          "name"="userId",
     *          "dataType"="int",
     *          "requirement"="\d+",
     *          "description"="User identifier"
     *      }
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
    public function editGroupAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Delete group
     *
     * @Route("groups/{groupId}", requirements={"groupId": "\d+"})
     * @Method({"DELETE"})
     *
     * @ApiDoc(
     *  description="Delete group",
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteGroupAction(Request $request)
    {
        return $this->success();
    }

    /**
     * Get group members
     *
     * @Route("groups/{groupId}/members")
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="",
     *  tags={},
     *  section="",
     *  requirements={
     *      {
     *          "name"="limit",
     *          "dataType"="int",
     *          "requirement"="\d+",
     *          "description"="How many items to return",
     *
     *      },
     *      {
     *          "name"="offset",
     *          "dataType"="int",
     *          "requirement"="\d+",
     *          "description"="Number of items to skip"
     *      },
     *      {
     *          "name"="query",
     *          "dataType"="string",
     *          "requirement"="\w+",
     *          "description"="Search query"
     *      },
     *      {
     *          "name"="userId",
     *          "dataType"="int",
     *          "requirement"="\d+",
     *          "description"="User identifier"
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
     * @param string $groupId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function membersAction(Request $request, $groupId)
    {
        try {
            $queryValidator = new QueryValidator(array(
                'query' => $request->query->get('query'),
                'limit' => $request->query->get('limit'),
                'offset' => $request->query->get('offset')
            ));
            $userValidator = new UserValidator(array(
                'userId' => $request->query->get('userId')
            ));
        } catch (MissingOptionsException $e) {
            return $this->invalid($e->getMessage());
        } catch (InvalidOptionsException $e) {
            return $this->invalid($e->getMessage());
        }

        $groupId = (int) $groupId;
        $userId = (int) $userValidator->getValue('userId');
        $offset = (int) $queryValidator->getValue('offset');
        $limit = (int) $queryValidator->getValue('limit');
        $query = $queryValidator->getValue('query');

        $groupManager = $this->get('manager.group');
        $members = $groupManager->getMembers($groupId, $userId, $limit, $offset, $query);

        return $this->success($members);
    }
}