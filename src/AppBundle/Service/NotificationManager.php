<?php


namespace AppBundle\Service;

/**
 * Class NotificationManager
 *
 * @package AppBundle\Service
 */
class NotificationManager extends BaseManager
{
    const NOTIFICATION_OBJECT_TYPE_POST = 'post';
    const NOTIFICATION_OBJECT_TYPE_COMMENT = 'comment';
    const NOTIFICATION_OBJECT_TYPE_INTEREST = 'interest';

    const NOTIFICATION_OBJECT_ACTION_FOLLOW = 'follow';
    const NOTIFICATION_OBJECT_ACTION_UNFOLLOW = 'unfollow';
    const NOTIFICATION_OBJECT_ACTION_UPVOTE = 'upvote';
    const NOTIFICATION_OBJECT_ACTION_DOWNVOTE = 'downvote';
    const NOTIFICATION_OBJECT_ACTION_COMMENT = 'comment';
    const NOTIFICATION_OBJECT_ACTION_FAVORITE = 'favorite';

    /**
     * @param $userId
     * @param $objectType
     * @param $objectAction
     * @param $objectId
     * @param $people
     */
    public function sendNotification($userId, $objectType, $objectAction, $objectId, $people)
    {
        $userManager = $this->container->get('manager.user');
        $userDevices = $userManager->getDevices($userId);

        $rPeople = array();
        $notificationId = $userManager->getNotificationId($userId);

        foreach ($people as $person) {
            $rPeople[] = $userManager->formatUser($person);
        }

        $objectData = array(
            'type' => 'notification',
            'data' => array(
                'objectId' => $objectId,
                'objectType' => $objectType,
                'type' => $objectAction,
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
            'registration_ids' => $userDevices
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        curl_exec($ch);
        curl_close($ch);
    }
}