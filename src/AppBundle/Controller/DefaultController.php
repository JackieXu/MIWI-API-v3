<?php


namespace AppBundle\Controller;

use RMS\PushNotificationsBundle\Message\AndroidMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * Handles miscellaneous stuff.
 *
 * @package AppBundle\Controller
 */
class DefaultController extends BaseController
{
    /**
     * Catches all OPTIONS requests
     *
     * @Route("/{any}")
     * @Method({"OPTIONS"})
     *
     * @return Response
     */
    public function catchOptionsAction()
    {
        return $this->success();
    }

    /**
     * Push notification test system
     *
     * @Route("users/{userId}/push-notification-tester", requirements={"userId": "\d+"})
     *
     * @param Request $request
     * @param string $userId
     * @return Response
     */
    public function notificationTesterAction(Request $request, $userId)
    {
        switch ($request->getMethod()) {
            case 'GET':
                $userId = (int) $userId;
                $interestManager = $this->get('manager.interest');
                $userManager = $this->get('manager.user');

                $users = $userManager->getUsers();
                $posts = $userManager->getUserPosts($userId, 5, 0, 0, '');
                $comments = $userManager->getUserComments($userId);
                $interests = $interestManager->getUserInterests($userId);

                $objects = array(
                    'interest' => array(
                        'follow',
                        'unfollow'
                    ),
                    'post' => array(
                        'comment',
                        'upvote',
                        'downvote',
                        'favorite'
                    ),
                    'comment' => array(
                        'upvote',
                        'downvote'
                    )
                );

                return $this->render(':default:notifications.html.twig', array(
                    'userId' => $userId,
                    'data' => array(
                        'users' => $users,
                        'posts' => $posts,
                        'comments' => $comments,
                        'interests' => $interests
                    ),
                    'objects' => $objects,
                ));
                break;
            case 'POST':
                $userId = (int) $request->request->get('userId');
                $objectId = (int) $request->request->get('objectId');
                $objectType = $request->request->get('objectType');
                $type = $request->request->get('type');
                $people = explode(',', $request->request->get('people'));
                $rPeople = array();
                $userManager = $this->get('manager.user');

                foreach ($people as $person) {
                    $rPeople[] = $userManager->formatUser((int) $person);
                }

                $devices = $userManager->getDevices($userId);
                $notificationId = $userManager->getNotificationId($userId);

                $objectData = array(
                    'type' => 'notification',
                    'data' => array(
                        'objectId' => $objectId,
                        'objectType' => $objectType,
                        'type' => $type,
                        'people' => $rPeople,
                        'id' => $notificationId,
                        'date' => time()
                    )
                );

                $headers = array(
                    "Content-Type: application/json",
                    "Authorization: key=AIzaSyCvn3Vbcm7wuFiZyXbRS0fSXeboCkK0mxg"
                );

                $data = array(
                    'data' => $objectData,
                    'registration_ids' => $devices
                );

                $this->get('logger')->log('info', json_encode($data));

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                $response = curl_exec($ch);
                curl_close($ch);

                return $this->success($response);
            default:
                return $this->invalid();
        }
    }
}
