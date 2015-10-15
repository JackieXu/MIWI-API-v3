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
     * @param string $status
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
     * @param int $profileId
     * @param int $userId
     * @param bool $wantsExtendedProfile
     * @return array|bool
     */
    public function getProfile($profileId, $userId, $wantsExtendedProfile)
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
            'userId' => $profileId
        ));

        if ($profile) {

            if ($profileId !== $userId) {
                $isFollowing = $this->sendCypherQuery('
                    MATCH   (u:USER)-[r:IS_FOLLOWING]->(f:USER)
                    WHERE   id(u) = {userId}
                    AND     id(f) = {followId}
                    RETURN  count(r) as c
                ', array(
                    'userId' => $userId,
                    'followId' => $profileId
                ));


                $profile[0]['isFollowing'] = $isFollowing[0]['c'] === 1;
            }

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
     * @param $interestId
     * @param $query
     * @return array
     * @throws \Exception
     */
    public function getUserFavoritedPosts($userId, $limit, $offset, $interestId, $query)
    {
        if ($interestId === 0) {
            $posts = $this->sendCypherQuery('
                MATCH   (u:USER)-[:HAS_FAVORITED]->(p:CONTENT)
                WHERE   id(u) = {userId}
                AND     p.title =~ {query}
                RETURN  id(p) as id,
                        p.image as image,
                        p.title as title,
                        p.upvotes as upvotes,
                        p.downvotes as downvotes,
                        p.comments as comments,
                        SUBSTRING(p.body, 0, 200) as body,
                        p.user as author
                SKIP    {offset}
                LIMIT   {limit}
            ', array(
                'userId' => $userId,
                'limit' => $limit,
                'offset' => $offset,
                'query' => '(?i)'.$query.'.*',
            ));
        } else {
            $posts = $this->sendCypherQuery('
                MATCH   (u:USER)-[:HAS_FAVORITED]->(p:CONTENT)-[:ASSOCIATED_WITH]->(i:INTEREST)
                WHERE   id(u) = {userId}
                AND     id(i) = {interestId}
                AND     p.title =~ {query}
                RETURN  id(p) as id,
                        p.image as image,
                        p.title as title,
                        p.upvotes as upvotes,
                        p.downvotes as downvotes,
                        p.comments as comments,
                        SUBSTRING(p.body, 0, 200) as body,
                        p.user as author
                SKIP    {offset}
                LIMIT   {limit}
            ', array(
                'userId' => $userId,
                'limit' => $limit,
                'offset' => $offset,
                'query' => '(?i)'.$query.'.*',
                'interestId' => $interestId
            ));
        }

        $postData = array();

        foreach ($posts as $post) {
            $postData[] = $this->container->get('formatter')->formatContent($post, $userId);
        }

        return $postData;
    }

    /**
     * Get user's posts
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @param int $interestId
     * @param string $query
     * @return array
     * @throws \Exception
     */
    public function getUserPosts($userId, $limit, $offset, $interestId, $query)
    {
        if ($interestId === 0) {
            $posts = $this->sendCypherQuery('
                MATCH   (u:USER)-[:HAS_POSTED]->(p:POST)
                WHERE   id(u) = {userId}
                AND     p.title =~ {query}
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
                'offset' => $offset,
                'query' => '(?i)'.$query.'.*'
            ));
        } else {
            $posts = $this->sendCypherQuery('
                MATCH   (u:USER)-[:HAS_POSTED]->(p:POST)-[:ASSOCIATED_WITH]->(i:INTEREST)
                WHERE   id(u) = {userId}
                AND     id(i) = {interestId}
                AND     p.title =~ {query}
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
                'offset' => $offset,
                'query' => '(?i)'.$query.'.*',
                'interestId' => $interestId
            ));
        }

        $postData = array();

        foreach ($posts as $post) {
            $postData[] = $this->container->get('formatter')->formatContent($post, $userId);
        }

        return $postData;
    }

    /**
     * Get user's followers
     *
     * @param int $userId
     * @param int $offset
     * @param int $limit
     * @param string $query
     * @return array
     */
    public function getUserFollowers($userId, $offset, $limit, $query)
    {
        $people = $this->sendCypherQuery('
            MATCH   (u:USER)-[:IS_FOLLOWING]->(f:USER)
            WHERE   id(u) = {userId}
            AND     f.name =~ {query}
            RETURN  id(f) as id,
                    f.firstName as firstName,
                    f.lastName as lastName,
                    f.image as image
            SKIP    {offset}
            LIMIT   {limit}
        ', array(
            'userId' => $userId,
            'query' => '(?i)'.$query.'.*',
            'limit' => $limit,
            'offset' => $offset
        ));

        return $people;
    }

    /**
     * Get people following the user
     *
     * @param int $userId
     * @param int $offset
     * @param int $limit
     * @param string $query
     * @return array
     * @throws \Exception
     */
    public function getUserFollowing($userId, $offset, $limit, $query)
    {
        $people = $this->sendCypherQuery('
            MATCH   (u:USER)-[:IS_FOLLOWING]->(f:USER)
            WHERE   id(f) = {userId}
            AND     u.name =~ {query}
            RETURN  id(u) as id,
                    u.firstName as firstName,
                    u.lastName as lastName,
                    u.image as image
            SKIP    {offset}
            LIMIT   {limit}
        ', array(
            'userId' => $userId,
            'query' => '(?i)'.$query.'.*',
            'limit' => $limit,
            'offset' => $offset
        ));

        return $people;
    }

    /**
     * @param $userId
     * @param $limit
     * @param $offset
     * @param $interestId
     * @param $query
     * @return array
     * @throws \Exception
     */
    public function getUserGroups($userId, $limit, $offset, $interestId, $query)
    {
        if ($interestId === 0) {
            $groups = $this->sendCypherQuery('
                MATCH   (u:USER)-[:MEMBER_OF]->(g:GROUP)
                WHERE   id(u) = {userId}
                AND     g.title =~ {query}
                RETURN  id(g) as id,
                        g.title as title,
                        g.members as memberCount,
                        g.image as image,
                        SUBSTRING(g.body, 0, 200) as body,
                        "group" as type
                SKIP    {offset}
                LIMIT   {limit}
            ', array(
                'userId' => $userId,
                'limit' => $limit,
                'offset' => $offset,
                'query' => '(?i)'.$query.'.*',
            ));
        } else {
            $groups = $this->sendCypherQuery('
                MATCH   (u:USER)-[:MEMBER_OF]->(g:GROUP)-[:ASSOCIATED_WITH]->(i:INTEREST)
                WHERE   id(u) = {userId}
                AND     id(i) = {interestId}
                AND     g.title =~ {query}
                RETURN  id(g) as id,
                        g.title as title,
                        g.members as memberCount,
                        g.image as image,
                        SUBSTRING(g.body, 0, 200) as body,
                        "group" as type
                SKIP    {offset}
                LIMIT   {limit}
            ', array(
                'userId' => $userId,
                'limit' => $limit,
                'offset' => $offset,
                'query' => '(?i)'.$query.'.*',
                'interestId' => $interestId
            ));
        }

        return $groups;
    }

    public function getUserEvents($userId, $limit, $offset, $interestId, $query)
    {
        if ($interestId === 0) {
            $events = $this->sendCypherQuery('
                MATCH   (u:USER)-[:IS_ATTENDING]->(e:EVENT)
                WHERE   id(u) = {userId}
                AND     e.title =~ {query}
                RETURN  id(e) as id,
                        e.title as title,
                        e.members as memberCount,
                        e.image as image,
                        SUBSTRING(e.body, 0, 200) as body,
                        "event" as type
                SKIP    {offset}
                LIMIT   {limit}
            ', array(
                'userId' => $userId,
                'limit' => $limit,
                'offset' => $offset,
                'query' => '(?i)'.$query.'.*'
            ));
        } else {
            $events = $this->sendCypherQuery('
                MATCH   (u:USER)-[:IS_ATTENDING]->(e:EVENT)-[:ASSOCIATED_WITH]->(i:INTEREST)
                WHERE   id(u) = {userId}
                AND     id(i) = {interestId}
                AND     e.title =~ {query}
                RETURN  id(e) as id,
                        e.title as title,
                        e.members as memberCount,
                        e.image as image,
                        SUBSTRING(e.body, 0, 200) as body,
                        "event" as type
                SKIP    {offset}
                LIMIT   {limit}
            ', array(
                'userId' => $userId,
                'limit' => $limit,
                'offset' => $offset,
                'query' => '(?i)'.$query.'.*',
                'interestId' => $interestId
            ));
        }

        return $events;
    }

    public function getProfileSettings($userId)
    {
        $settings = $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
            RETURN  u.location as location,
                    u.firstName as firstName,
                    u.lastName as lastName
        ', array(
            'userId' => $userId
        ));

        if ($settings) {
            $settings = $settings[0];

            return array(
                'location' => $settings['location'] ? $settings['location'] : '',
                'firstName' => $settings['firstName'] ? $settings['firstName'] : '',
                'lastName' => $settings['lastName'] ? $settings['lastName'] : ''
            );
        }

        return false;
    }

    public function getSecuritySettings($userId)
    {
        $settings = $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
            RETURN  u.email as email
        ', array(
            'userId' => $userId
        ));

        if ($settings) {
            $settings = $settings[0];

            return array(
                'email' => $settings['email'] ? $settings['email'] : ''
            );
        }

        return false;
    }

    public function getNotificationSettings($userId)
    {
        $settings = $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
            RETURN  u.emailVotes as emailVotes,
                    u.emailComments as emailComments,
                    u.appVotes as appVotes,
                    u.appComments as appComments
        ', array(
            'userId' => $userId
        ));

        if ($settings) {
            $settings = $settings[0];

            return array(
                'emailVotes' => $settings['emailVotes'] ? $settings['emailVotes'] : false,
                'emailComments' => $settings['emailComments'] ? $settings['emailComments'] : false,
                'appVotes' => $settings['appVotes'] ? $settings['appVotes'] : false,
                'appComments' => $settings['appComments'] ? $settings['appComments'] : false,
            );
        }

        return false;
    }

    public function updateProfileSettings($userId, $settings)
    {
        $acceptedSettings = array(
            'location',
            'firstName',
            'lastName'
        );

        $cypherString = '
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
        ';

        foreach ($acceptedSettings as $setting) {
            if (array_key_exists($setting, $settings)) {
                $cypherString .= sprintf('
                    SET     u.%s = {%s}
                ', $setting, $setting);
            }
        }

        $cypherString .= '
            RETURN  id(u) as id
        ';

        $settings['userId'] = $userId;

        $userId = $this->sendCypherQuery($cypherString, $settings);

        if ($userId) {
            return $settings[0]['id'];
        }

        return false;
    }

    public function updateSecuritySettings($userId, $settings)
    {
        if (array_key_exists('email', $settings)) {
            if (filter_var($settings['email'], FILTER_VALIDATE_EMAIL) !== false) {
                $userId = $this->sendCypherQuery('
                    MATCH   (u:USER)
                    WHERE   id(u) = {userId}
                    SET     u.email = {email}
                    RETURN  id(u) as id
                ', array(
                    'userId' => $userId,
                    'email' => $settings['email']
                ));
            } else {
                return false;
            }
        }

        if (array_key_exists('password', $settings)) {
            $userId = $this->sendCypherQuery('
                MATCH   (u:USER)
                WHERE   id(u) = {userId}
                SET     u.password = {password}
                RETURN  id(u) as id
            ', array(
                'userId' => $userId,
                'password' => password_hash($settings['password'], PASSWORD_BCRYPT)
            ));
        }

        if ($userId) {
            return $settings[0]['id'];
        }

        return false;
    }

    public function updateNotificationSettings($userId, $settings)
    {
        $acceptedSettings = array(
            'emailVotes',
            'emailComments',
            'appVotes',
            'appMentions'
        );

        $cypherString = '
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
        ';

        foreach ($acceptedSettings as $setting) {
            if (array_key_exists($setting, $settings)) {
                $cypherString .= sprintf('
                    SET     u.%s = {%s}
                ', $setting, $setting);
            }
        }

        $cypherString .= '
            RETURN  id(u) as id
        ';

        $settings['userId'] = $userId;

        $userId = $this->sendCypherQuery($cypherString, $settings);

        if ($userId) {
            return true;
        }

        return false;
    }

    public function updateSettings($userId, $settings)
    {
        $acceptedSettings = array(
            'location',
            'firstName',
            'lastName',
            'email',
            'password',
            'emailMentions',
            'emailVotes',
            'emailComments',
            'appMentions',
            'appVotes',
            'appMentions'
        );

        $cypherString = '
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
        ';

        foreach ($acceptedSettings as $setting) {
            if (array_key_exists($setting, $settings)) {
                $cypherString .= sprintf('
                    SET     u.%s = {%s}
                ', $setting, $setting);
            }
        }

        $cypherString .= '
            RETURN  id(u) as id
        ';

        $settings['userId'] = $userId;

        $userId = $this->sendCypherQuery($cypherString, $settings);

        if ($userId) {
            return $settings[0]['id'];
        }

        return false;
    }

    /**
     * Add device
     * @param $userId
     * @param $deviceId
     * @param $deviceType
     * @return array|null
     * @throws \Exception
     */
    public function addDevice($userId, $deviceId, $deviceType)
    {
        switch ($deviceType) {
            case 'ios':
                $user = $this->sendCypherQuery('
                    MATCH       (u:USER)
                    WHERE       id(u) = {userId}
                    WITH        u,
                                CASE    {deviceId} IN u.iosDevices
                                WHEN    true
                                THEN    u.iosDevices
                                ELSE    u.iosDevices + {deviceId}
                                END
                                AS newDevices
                    SET         u.iosDevices = newDevices
                    RETURN      id(u) as id
                ', array(
                    'userId' => $userId,
                    'deviceId' => $deviceId
                ));
                break;
            case 'android':
                $user = $this->sendCypherQuery('
                    MATCH       (u:USER)
                    WHERE       id(u) = {userId}
                    WITH        u,
                                CASE    {deviceId} IN u.androidDevices
                                WHEN    true
                                THEN    u.androidDevices
                                ELSE    u.androidDevices + {deviceId}
                                END
                                AS newDevices
                    SET         u.androidDevices = newDevices
                    RETURN      id(u) as id
                ', array(
                    'userId' => $userId,
                    'deviceId' => $deviceId
                ));
                break;
            default:
                $user = null;
        }

        return $user;
    }

    public function getUserComments($userId)
    {
        $comments = $this->sendCypherQuery('
                MATCH   (u:USER)-[:COMMENTED_WITH]->(c:COMMENT)
                WHERE   id(u) = {userId}
                RETURN  id(c) as id,
                        c.text as text
                LIMIT   5
            ', array(
            'userId' => $userId,
        ));

        return $comments;
    }

    public function getUsers()
    {
        $users = $this->sendCypherQuery('
            MATCH   (u:USER)
            RETURN  id(u) as id,
                    u.firstName + " " + u.lastName as name
            ORDER BY id DESC
            LIMIT   5
        ', array(
            'userId' => 1
        ));

        return $users;
    }

    public function getDevices($userId)
    {
        $devices = $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
            RETURN  u.androidDevices as devices
        ', array(
            'userId' => $userId
        ));

        if ($devices) {
            return array_key_exists('devices', $devices[0]) ? $devices[0]['devices'] : array();
        }

        return array();
    }

    public function formatUser($userId)
    {
        $user = $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
            RETURN  id(u) as id,
                    u.firstName as firstName,
                    u.lastName as lastName,
                    u.image as image
        ', array(
            'userId' => $userId
        ));

        if ($user) {
            return $user[0];
        }

        return false;
    }

    /**
     * Get user's notification id
     *
     * This is an incrementing number to keep track of a user's
     * notifications.
     *
     * @param $userId
     * @return mixed
     * @throws \Exception
     */
    public function getNotificationId($userId)
    {
        $id = $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
            SET     u.notitifcationId = u.notificationId + 1
            RETURN  u.notificationId as notificationId
        ', array(
            'userId' => $userId
        ));

        return $id[0]['notificationId'];
    }

    /**
     * Update user image
     *
     * Updates a user's profile picture. Note that $string is a base64
     * representation of an image.
     *
     * @param int $userId
     * @param string $string
     * @return bool
     * @throws \Exception
     */
    public function updateImage($userId, $string)
    {
        $templateString = '%s/img/node/%s';
        $saveRoot = '/var/www/av3/web';
        $webRoot = 'http://av3.miwi.com';
        $fileName = uniqid();

        if (filter_var($string, FILTER_VALIDATE_URL) !== false) {
            return $string;
        }

        if (strpos($string, ',')) {

            $data = explode(',', $string);
            $image = base64_decode($data[1]);

        } else {

            $image = base64_decode($string);

        }

        $file = finfo_open();
        $mimeType = finfo_buffer($file, $image, FILEINFO_MIME_TYPE);
        finfo_close($file);

        $mimeArray = explode('/', $mimeType);
        $extension = array_pop($mimeArray);

        $saveLocation = sprintf($templateString, $saveRoot, $fileName . '_orig.' . $extension);
        $webLocation = sprintf($templateString, $webRoot, $fileName . '_orig.' . $extension);

        $location = $this->saveData($saveLocation, $webLocation, $image);

        $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
            SET     u.image = {image}
            RETURN  id(u) as id
        ', array(
            'userId' => $userId,
            'image' =>$location
        ));

        return $location;
    }

    private function saveData($saveLocation, $webLocation, $content)
    {
        $temp = tempnam(sys_get_temp_dir(), 'temp');

        if (!($f = @fopen($temp, 'wb'))) {
            $temp = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('temp');
            if (!($f = @fopen($temp, 'wb'))) {
                trigger_error(sprintf('Error writing temp file `%s`', $temp), E_USER_WARNING);
                return false;
            }
        }

        @fwrite($f, $content);
        @fclose($f);

        if (!@rename($temp, $saveLocation)) {
            @unlink($saveLocation);
            @rename($temp, $saveLocation);
        }

        @chmod($saveLocation, 0777);

        return $webLocation;
    }

    public function deleteInterest($userId, $interestId)
    {
        $interest = $this->sendCypherQuery('
            MATCH   (u:USER)-[r:LIKES]->(i:INTEREST)
            WHERE   id(u) = {userId}
            AND     id(i) = {interestId}
            DELETE  r
            RETURN  id(u)
        ', array(
            'userId' => $userId,
            'interestId' => $interestId
        ));

        if ($interest) {
            return true;
        }

        return false;
    }

    /**
     * Favorite an item
     *
     * Functions as a toggle, which means that an already favorited item
     * will be removed if favorited again.
     *
     * @param int $itemId
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
    public function favoriteItem($itemId, $userId)
    {
        $isFavorited = $this->sendCypherQuery('
            MATCH   (u:USER)-[r:HAS_FAVORITED]->(i:ITEM)
            WHERE   id(u) = {userId}
            AND     id(i) = {itemId}
            RETURN  COUNT(r) as c
        ', array(
            'userId' => $userId,
            'itemId' => $itemId
        ));

        try {
            if ($isFavorited[0]['c'] === 0) {
                $r = $this->sendCypherQuery('
                    MATCH   (u:USER), (i:ITEM)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    SET     i.favorites = i.favorites + 1
                    CREATE  (u)-[:HAS_FAVORITED]->(i)
                    RETURN  i.favorites as favoriteCount
                ', array(
                    'itemId' => $itemId,
                    'userId' => $userId
                ));
                $r[0]['hasFavorited'] = true;
            } else {
                $r = $this->sendCypherQuery('
                    MATCH   (u:USER)-[r:HAS_FAVORITED]->(i:ITEM)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    SET     i.favorites = i.favorites - 1
                    DELETE  r
                    WITH    i
                    RETURN  i.favorites as favoriteCount
                ', array(
                    'userId' => $userId,
                    'itemId' => $itemId
                ));
                $r[0]['hasFavorited'] = false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return $r[0];
    }

    /**
     * Follow a user
     *
     * Functions as a toggle, which means that an already followed user
     * will not be followed anymore, if this method is called gain on the
     * same user.
     *
     * @param int $followId
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
    public function followUser($followId, $userId)
    {
        $isFollowing = $this->sendCypherQuery('
            MATCH   (u:USER)-[r:IS_FOLLOWING]->(f:USER)
            WHERE   id(u) = {userId}
            AND     id(f) = {followId}
            RETURN  COUNT(r) as c
        ', array(
            'userId' => $userId,
            'followId' => $followId
        ));

        try {
            if ($isFollowing[0]['c'] === 1) {
                $data = $this->sendCypherQuery('
                    MATCH   (u:USER)-[r:IS_FOLLOWING]->(f:USER)
                    WHERE   id(u) = {userId}
                    AND     id(f) = {followId}
                    DELETE  r
                    WITH    u,f
                    SET     f.followerCount = f.followerCount - 1
                    SET     u.followingCount = u.followingCount - 1
                    RETURN  f.followerCount as followerCount
                ', array(
                    'userId' => $userId,
                    'followId' => $followId
                ));
                $data[0]['isFollowing'] = false;

            } else {
                $data = $this->sendCypherQuery('
                    MATCH   (u:USER), (f:USER)
                    WHERE   id(u) = {userId}
                    AND     id(f) = {followId}
                    CREATE  (u)-[:IS_FOLLOWING]->(f)
                    WITH    u,f
                    SET     f.followerCount = f.followerCount + 1
                    SET     u.followingCount = u.followingCount + 1
                    RETURN  f.followerCount as followerCount
                ', array(
                    'followId' => $followId,
                    'userId' => $userId
                ));
                $data[0]['isFollowing'] = true;
            }

            return $data[0];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete user
     *
     * Deletes all traces of a user having existed. All his comments and posts
     * will be deleted. Upvotes and downvotes are kept.
     *
     * @param int $userId
     * @return bool
     */
    public function deleteUser($userId)
    {
        try {
            $this->sendCypherQuery('
                MATCH   (u:USER)-[x]-(r)
                WHERE   id(u) = {userId}
                DELETE  x, u
                WITH    r
                MATCH   (i:ITEM)
                WHERE   i.user = {userId}
                DELETE  i
                WITH    i
                MATCH   (c:COMMENT)
                WHERE   c.user = {userId}
                DELETE  c
            ', array(
                'userId' => $userId
            ));
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
