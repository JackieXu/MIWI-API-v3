<?php


namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class InterestController
 *
 * @Route("/v3/")
 *
 * @package AppBundle\Controller
 */
class InterestController extends Controller
{
    /**
     * @Route("interests/")
     * @Method({"GET", "OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function overviewAction(Request $request)
    {
        $interestManager = $this->get('manager.interest');

        try {
            $interests = $interestManager->getInterests(0, 10);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400);
        }

        return $this->render(':default:test.html.twig', array(
            'interests' => $interests
        ));

//        return new JsonResponse($interests, 200);
    }
}
