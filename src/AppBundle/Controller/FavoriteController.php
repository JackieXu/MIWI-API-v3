<?php


namespace AppBundle\Controller;


use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function favoritesAction(Request $request)
    {
        return $this->invalid();
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