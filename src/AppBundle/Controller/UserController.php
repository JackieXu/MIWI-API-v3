<?php


namespace AppBundle\Controller;

use AppBundle\Validator\LimitValidator;
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
     *  authentication=true
     * )
     *
     * @param Request $request
     * @param string $userId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postsAction(Request $request, $userId)
    {
        try {
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
            $limitValidator = new LimitValidator($request->query->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $accessManager = $this->get('manager.access');
        $accessToken = $tokenValidator->getValue('accessToken');
        $limit = (int) $limitValidator->getValue('limit');
        $offset = (int) $limitValidator->getValue('offset');
        $userId = (int) $userId;

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $userManager = $this->get('manager.user');
            $posts = $userManager->getUserPosts($userId, $limit, $offset);

            return $this->success($posts);
        }

        return $this->forbidden();
    }
}