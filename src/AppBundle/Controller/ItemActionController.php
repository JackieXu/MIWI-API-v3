<?php


namespace AppBundle\Controller;


use AppBundle\Validator\CommentValidator;
use AppBundle\Validator\TokenValidator;
use AppBundle\Validator\UserValidator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\MissingOptionsException;

class ItemActionController extends BaseController
{
    /**
     * Upvotes an item
     *
     * @Route("items/{itemId}/upvote", requirements={"itemId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Upvote an item",
     *  tags={},
     *  section="items",
     *  requirements={
     *
     *  },
     *  parameters={
     *      {
     *          "name"="userId",
     *          "dataType"="int",
     *          "required"="true",
     *          "description"="User identifier"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when parameters are missing or invalid"
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @param string $itemId
     * @return Response
     */
    public function upvoteAction(Request $request, $itemId)
    {
        try {
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
            $userValidator = new UserValidator($request->request->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $accessManager = $this->get('manager.access');
        $accessToken = $tokenValidator->getValue('accessToken');
        $userId = (int) $userValidator->getValue('userId');
        $itemId = (int) $itemId;

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $timelineManager = $this->get('manager.timeline');
            $votes = $timelineManager->upvoteItem($userId, $itemId);

            return $this->success($votes);
        }

        return $this->forbidden();
    }

    /**
     * Downvotes an item
     *
     * @Route("items/{itemId}/downvote")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Downvote an item",
     *  tags={},
     *  section="items",
     *  requirements={
     *
     *  },
     *  parameters={
     *      {
     *          "name"="userId",
     *          "dataType"="int",
     *          "required"="true",
     *          "description"="User identifier"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when parameters are missing or invalid"
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @param string $itemId
     * @return Response
     */
    public function downvoteAction(Request $request, $itemId)
    {
        try {
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        try {
            $userValidator = new UserValidator($request->request->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $accessManager = $this->get('manager.access');
        $accessToken = $tokenValidator->getValue('accessToken');
        $userId = (int) $userValidator->getValue('userId');
        $itemId = (int) $itemId;

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $timelineManager = $this->get('manager.timeline');
            $votes = $timelineManager->downvoteItem($userId, $itemId);

            return $this->success($votes);
        }

        return $this->forbidden();
    }

    /**
     * Comments on an item
     *
     * @Route("items/{itemId}/comments", requirements={"itemId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Comment on an item",
     *  tags={},
     *  section="items",
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
     * @param string $itemId
     * @return Response
     */
    public function commentAction(Request $request, $itemId)
    {
        try {
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
            $commentValidator = new CommentValidator($request->request->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid(array(
                'error' => $e->getMessage()
            ));
        } catch (InvalidOptionsException $e) {
            return $this->invalid(array(
                'error' => $e->getMessage()
            ));
        }

        $itemId = (int) $itemId;
        $userId = (int) $commentValidator->getValue('userId');
        $text = $commentValidator->getValue('comment');
        $accessToken = $tokenValidator->getValue('accessToken');

        $accessManager = $this->get('manager.access');
        $contentManager = $this->get('manager.content');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $commentId = $contentManager->comment($userId, $itemId, $text);

            if ($commentId) {
                return $this->success(array(
                    'id' => $commentId
                ));
            }

            return $this->invalid();
        }

        return $this->unauthorized();
    }

    /**
     * Report an item
     *
     * @Route("items/{itemId}/report")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Report an item",
     *  tags={},
     *  section="items",
     *  requirements={
     *
     *  },
     *  parameters={
     *      {
     *          "name"="userId",
     *          "dataType"="int",
     *          "required"="true",
     *          "description"="User identifier"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when parameters are missing or invalid"
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @param string $itemId
     * @return Response
     */
    public function flagAction(Request $request, $itemId)
    {
        try {
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        try {
            $userValidator = new UserValidator($request->request->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $accessManager = $this->get('manager.access');
        $accessToken = $tokenValidator->getValue('accessToken');
        $userId = (int) $userValidator->getValue('userId');
        $itemId = (int) $itemId;

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $timelineManager = $this->get('manager.timeline');
            $item = $timelineManager->flagItem($userId, $itemId);

            if ($item) {
                return $this->success();
            }

            return $this->invalid();
        }

        return $this->unauthorized();
    }

    /**
     * Hide an item
     *
     * @Route("items/{itemId}/hide")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Hide an item",
     *  tags={},
     *  section="items",
     *  requirements={
     *
     *  },
     *  parameters={
     *      {
     *          "name"="userId",
     *          "dataType"="int",
     *          "required"="true",
     *          "description"="User identifier"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when parameters are missing or invalid"
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @param string $itemId
     * @return Response
     */
    public function hideAction(Request $request, $itemId)
    {
        try {
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $request->headers->get('accessToken')
            ));
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        try {
            $userValidator = new UserValidator($request->request->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        $accessManager = $this->get('manager.access');
        $accessToken = $tokenValidator->getValue('accessToken');
        $userId = (int) $userValidator->getValue('userId');
        $itemId = (int) $itemId;

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $timelineManager = $this->get('manager.timeline');
            $item = $timelineManager->hideItem($userId, $itemId);

            if ($item) {
                return $this->success();
            }

            return $this->invalid();
        }

        return $this->unauthorized();
    }

    /**
     * Share item
     *
     * @Route("items/{itemId}/share", requirements={"itemId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Share item",
     *  tags={},
     *  section="items",
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
     * @return Response
     */
    public function shareAction(Request $request)
    {
        return $this->invalid();
    }
}