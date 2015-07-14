<?php


namespace AppBundle\Service;


/**
 * Class UserManager
 *
 * @package AppBundle\Service
 */
class UserManager extends BaseManager
{
    /**
     * Sets user status
     *
     * @param int $userId
     * @param int $status
     * @return bool True on success, false on failure
     * @throws \Exception
     */
    public function setUserStatus($userId, $status)
    {
        $newStatus = $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
            SET     u.status = {status}
            RETURN  u.status as status
        ', array(
            'userId' => $userId,
            'status' => $status
        ));

        if ($newStatus) {
            return $newStatus[0]['status'] === $status;
        }

        return false;
    }

    /**
     * Gets user profile
     *
     * Returns a simple user profile consisting of an avatar and name, unless `$wantsExtendedProfile` set to true.
     *
     * @param int $userId
     * @param bool $wantsExtendedProfile
     * @return array|bool
     */
    public function getProfile($userId, $wantsExtendedProfile)
    {
        if ($wantsExtendedProfile) {
            $query = '
                MATCH   (u:USER)
                WHERE   id(u) = {userId}
                RETURN  u.firstName as firstName,
                        u.lastName as lastName,
                        u.firstName + " " + u.lastName as name,
                        u.image as image,
                        u.location as location,
                        u.followerCount as followerCount,
                        u.followingCount as followingCount
            ';
        } else {
            $query = '
                MATCH   (u:USER)
                WHERE   id(u) = {userId}
                RETURN  u.firstName as firstName,
                        u.lastName as lastName,
                        u.firstName + " " + u.lastName as name,
                        u.image as image
            ';
        }

        $profile = $this->sendCypherQuery($query, array(
            'userId' => $userId
        ));

        if ($profile) {
            return $profile[0];
        }

        return false;
    }
}
