<?php


namespace AppBundle\Controller;

use AppBundle\Validator\ItemValidator;
use AppBundle\Validator\LimitValidator;
use AppBundle\Validator\TokenValidator;
use AppBundle\Validator\UserValidator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * Class ItemController
 *
 * @package AppBundle\Controller
 */
class ItemController extends BaseController
{
    /**
     * Get item
     *
     * @Route("items/{itemId}", requirements={"itemId": "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get item",
     *  tags={},
     *  section="items",
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
     * @param string $itemId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, $itemId)
    {
        try {
            $userValidator = new UserValidator($request->query->all());
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

        $itemId = (int) $itemId;
        $userId = (int) $userValidator->getValue('userId');
        $accessToken = $tokenValidator->getValue('accessToken');
        $accessManager = $this->get('manager.access');
        $itemManager = $this->get('manager.content');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $item = $itemManager->get($itemId, $userId);

            if ($item) {
                return $this->success($item);
            }

            return $this->invalid(array(
                'error' => 'Invalid item'
            ));
        }

        return $this->unauthorized();
    }

    /**
     * Get item comments
     *
     * @Route("items/{itemId}/comments", requirements={"itemId": "\d+"})
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get item comments",
     *  tags={},
     *  section="items",
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
     * @param string $itemId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function commentsAction(Request $request, $itemId)
    {
        try {
            $userValidator = new UserValidator(array(
                'userId' => $request->query->get('userId')
            ));
            $limitValidator = new LimitValidator(array(
                'limit' => $request->query->get('limit', '30'),
                'offset' => $request->query->get('offset', '0')
            ));
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

        $itemId = (int) $itemId;
        $userId = (int) $userValidator->getValue('userId');
        $accessToken = $tokenValidator->getValue('accessToken');
        $accessManager = $this->get('manager.access');
        $itemManager = $this->get('manager.content');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $offset = (int) $limitValidator->getValue('offset');
            $limit = (int) $limitValidator->getValue('limit');

            $comments = $itemManager->getComments($itemId, $userId, $offset, $limit);

            return $this->success($comments);
        }

        return $this->unauthorized();
    }

    /**
     * Create new item
     *
     * @Route("items")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Create new item",
     *  tags={},
     *  section="items",
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
    public function createAction(Request $request)
    {
        try {
            $itemValidator = new ItemValidator($request->request->all());
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

        $userId = (int) $itemValidator->getValue('userId');
        $interestId = (int) $itemValidator->getValue('interestId');
        $title = $itemValidator->getValue('title');
        $body = $itemValidator->getValue('body');
        $images = array();
        $imagesRes = trim($itemValidator->getValue('images'));
        if (!empty($imagesRes)) {
            $images = explode(',', $imagesRes);
        }
        $accessManager = $this->get('manager.access');
        $accessToken = $tokenValidator->getValue('accessToken');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $itemManager = $this->get('manager.content');
            $item = $itemManager->create($title, $body, $images, $userId, $interestId);

            if ($item) {
                return $this->success($item);
            }

            return $this->invalid();
        }

        return $this->unauthorized();
    }

    /**
     * Edit item
     *
     * @Route("items/{itemId}", requirements={"itemId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="Edit item",
     *  tags={},
     *  section="items",
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
     * @param string $itemId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $itemId)
    {
        try {
            $itemValidator = new ItemValidator($request->request->all());
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

        $itemId = (int) $itemId;
        $userId = (int) $itemValidator->getValue('userId');
        $interestId = (int) $itemValidator->getValue('interestId');
        $title = $itemValidator->getValue('title');
        $body = $itemValidator->getValue('body');
        $images = $itemValidator->getValue('images');

        $accessManager = $this->get('manager.access');
        $accessToken = $tokenValidator->getValue('accessToken');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $itemManager = $this->get('manager.content');
            $item = $itemManager->edit($itemId, $title, $body, $images, $userId, $interestId);

            if ($item) {
                return $this->success($item);
            }

            return $this->invalid();
        }

        return $this->unauthorized();
    }

    /**
     * Delete item
     *
     * @Route("items/{itemId}", requirements={"itemId": "\d+"})
     * @Method({"DELETE"})
     *
     * @ApiDoc(
     *  description="Delete item",
     *  tags={},
     *  section="items",
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
    public function deleteAction(Request $request)
    {
        return $this->invalid();
    }
}