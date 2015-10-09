<?php


namespace AppBundle\Controller;

use AppBundle\Validator\ItemValidator;
use AppBundle\Validator\TokenValidator;
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
     * Get items
     *
     * @Route("items")
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  description="Get items",
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
    public function overviewAction(Request $request)
    {
        return $this->invalid();
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

        $userId = $itemValidator->getValue('userId');
        $interestId = $itemValidator->getValue('interestId');
        $title = $itemValidator->getValue('title');
        $body = $itemValidator->getValue('body');
        $images = $itemValidator->getValue('images');

        $accessManager = $this->get('manager.access');
        $accessToken = $tokenValidator->getValue('accessToken');

        if ($accessManager->hasAccessToUser($accessToken, $userId)) {
            $itemManager = $this->get('manager.content');
            $itemId = $itemManager->create($title, $body, $images, $userId, $interestId);

            if ($itemId) {
                return $this->success(array(
                    'id' => $itemId
                ));
            }

            return $this->invalid();
        }

        return $this->unauthorized();
    }

    /**
     * Edit item
     *
     * @Route("items/{itemId}", requirements={"itemId": "\d+"})
     * @Method({"PATCH"})
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        return $this->invalid();
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