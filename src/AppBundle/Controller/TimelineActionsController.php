<?php


namespace AppBundle\Controller;

use AppBundle\Validator\TokenValidator;
use AppBundle\Validator\UserValidator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * Class TimelineActionsController
 *
 * @package AppBundle\Controller
 */
class TimelineActionsController extends BaseController
{
    /**
     * Upvotes an item
     *
     * @Route("items/{itemId}/upvote", requirements={"itemId": "\d+"})
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  description="",
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
     *      "200"="OK"
     *  },
     *  authentication=true
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function upvoteAction(Request $request)
    {
        $accessToken = $request->headers->get('accessToken', '');
        $accessManager = $this->get('manager.access');

        try {
            $tokenValidator = new TokenValidator(array(
                'accessToken' => $accessToken
            ));
        } catch (MissingOptionsException $e) {
            return $this->invalid();
        } catch (InvalidOptionsException $e) {
            return $this->invalid();
        }

        try {
            $userValidator = new UserValidator($request->request->all());
        } catch (MissingOptionsException $e) {
            return $this->invalid(array(
                'error' => 'Missing user id'
            ));
        } catch (InvalidOptionsException $e) {
            return $this->invalid(array(
                'error' => 'Invalid user id'
            ));
        }

        if ($accessManager->hasAccessToUser($tokenValidator->getValue('accessToken'), (int) $userValidator->getValue('userId'))) {

        }

        return $this->forbidden();
    }

    /**
     * Downvotes an item
     *
     * @Route("items/{itemId}/downvote")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function downvoteAction(Request $request)
    {

    }

    /**
     * Comments on an item
     *
     * @param Request $request
     */
    public function commentAction(Request $request)
    {

    }
}
