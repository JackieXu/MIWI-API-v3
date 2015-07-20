<?php


namespace AppBundle\Controller;


use AppBundle\Validator\LimitValidator;
use AppBundle\Validator\TokenValidator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class FavoriteController extends BaseController
{
    /**
     * Favorite an item
     *
     * @Route("users/{userId}/favorites", requirements={"userId": "\d+"})
     * @Method({"PUT"})
     *
     * @ApiDoc(
     *  description="Favorite an item",
     *  tags={},
     *  section="users",
     *  requirements={
     *      {
     *          "name"="itemId",
     *          "dataType"="int",
     *          "requirement"="\d+",
     *          "description"="Item identifier"
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
     * @return Response
     */
    public function favoriteAction(Request $request)
    {
        return $this->invalid();
    }

    /**
     * Get list of favorites
     *
     * @Route("users/{userId}/favorites", requirements={"userId": "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get list of favorited items",
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
     *      400="Returned when parameters are invalid or missing",
     *      401="Returned when not authenticated",
     *      403="Returned when not authorized",
     *      500="Returned when an error occured"
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @param string $userId
     * @return Response
     */
    public function favoritesAction(Request $request, $userId)
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
            $favorites = $userManager->getUserFavoritedPosts($userId, $limit, $offset);

            return $this->success($favorites);
        }

        return $this->forbidden();
    }

    /**
     * Delete favorited item
     *
     * @Route("users/{userId}/favorites", requirements={"userId": "\d+"})
     * @Method({"DELETE"})
     *
     * @ApiDoc(
     *  description="Remove favorited item",
     *  tags={},
     *  section="users",
     *  requirements={
     *      {
     *          "name"="itemId",
     *          "dataType"="int",
     *          "requirement"="\d+",
     *          "description"="Item identifier"
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
     * @return Response
     */
    public function deleteFavoriteAction(Request $request)
    {
        return $this->invalid();
    }
}