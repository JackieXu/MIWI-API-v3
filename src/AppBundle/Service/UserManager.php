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

    /**
     * Get user's favorited posts
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getUserFavoritedPosts($userId, $limit, $offset)
    {
        $posts = $this->sendCypherQuery('
            MATCH   (u:USER)-[:HAS_FAVORITED]->(p:POST)
            WHERE   id(u) = {userId}
            RETURN  id(p) as id,
                    p.image as image,
                    p.title as title,
                    p.upvotes as upvotes,
                    p.downvotes as downvotes,
                    p.comments as comments,
                    SUBSTRING(p.body, 0, 200) as body,
                    labels(p) as labels
            SKIP    {offset}
            LIMIT   {limit}
        ', array(
            'userId' => $userId,
            'limit' => $limit,
            'offset' => $offset
        ));

        $postData = array();

        foreach ($posts as $post) {
            if (in_array('POST', $post['labels'])) {
                $label = 'post';
            } else {
                $label = 'article';
            }
            $postData[] = array(
                'id' => $post['id'],
                'title' => $post['title'],
                'upvotes' => (int) $post['upvotes'],
                'downvotes' => (int) $post['downvotes'],
                'comments' => (int)$post ['comments'],
                'body' => $post['body'],
                "type" => $label
            );
        }

        return $postData;
    }

    /**
     * Get user's posts
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getUserPosts($userId, $limit, $offset)
    {
        $posts = $this->sendCypherQuery('
            MATCH   (u:USER)-[:HAS_POSTED]->(p:POST)
            WHERE   id(u) = {userId}
            RETURN  id(p) as id,
                    p.image as image,
                    p.title as title,
                    p.upvotes as upvotes,
                    p.downvotes as downvotes,
                    p.comments as comments,
                    SUBSTRING(p.body, 0, 200) as body,
                    "post" as type
            SKIP    {offset}
            LIMIT   {limit}
        ', array(
            'userId' => $userId,
            'limit' => $limit,
            'offset' => $offset
        ));

        return $posts;
    }
}
