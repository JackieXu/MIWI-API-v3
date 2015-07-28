<?php


namespace AppBundle\Controller;

use AppBundle\Validator\FilterValidator;
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
}