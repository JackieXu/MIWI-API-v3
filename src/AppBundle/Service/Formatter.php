<?php


namespace AppBundle\Service;

/**
 * Class Formatter
 *
 * Handles node formatting into usable arrays.
 *
 * @package AppBundle\Service
 */
class Formatter extends BaseManager
{
    /**
     * Returns formatted content array
     *
     * @param array $content
     * @param integer $userId
     * @return array
     */
    public function formatContent($content, $userId)
    {
        $formattedAuthor = $this->formatUser($content['author']);
        $hasCommented = false;
        $isAdmin = $content['author'] === $userId;

        $isFavorited = count($this->sendCypherQuery('
            MATCH   (u:USER)-[r:HAS_FAVORITED]->(c:CONTENT)
            WHERE   id(u) = {userId}
            AND     id(c) = {contentId}
            RETURN  r
        ', array(
                'userId' => $userId,
                'contentId' => $content['id']
            ))) > 0;

        $hasUpvoted = count($this->sendCypherQuery('
            MATCH   (u:USER)-[r:HAS_VOTED]->(c:CONTENT)
            WHERE   id(u) = {userId}
            AND     id(c) = {contentId}
            AND     r.score = 1
            RETURN  u
        ', array(
            'userId' => $userId,
            'contentId' => $content['id']
        ))) > 0;

        $hasDownvoted = count($this->sendCypherQuery('
            MATCH   (u:USER)-[r:HAS_VOTED]->(c:CONTENT)
            WHERE   id(u) = {userId}
            AND     id(c) = {contentId}
            AND     r.score = -1
            RETURN  u
        ', array(
            'userId' => $userId,
            'contentId' => $content['id']
        ))) > 0;

        $images = array();
        if (is_array($content['images'])) {
            $images = $content['images'];
        } elseif (is_string($content['images'])) {
            $images = explode(',', $content['images']);

            if (is_string($images) && strlen($images) > 5) {
                $images = array($images);
            }
        }


        $images = array_filter($images);

        return array(
            'id' => $content['id'],
            'date' => $content['date'],
            'type' => in_array('ARTICLE', $content['labels']) ? 'article' : 'post',
            'body' => $content['body'],
            'interestId' => $content['interestId'],
            'upvotes' => $content['upvotes'],
            'downvotes' => $content['downvotes'],
            'images' => $images,
            "link" => $content['link'],
            'title' => $content['title'],
            'commentCount' => $content['comments'],
            'favoriteCount' => $content['favorites'],
            'hasCommented' => $hasCommented,
            'hasFavorited' => $isFavorited,
            'hasUpvoted' => $hasUpvoted,
            'hasDownvoted' => $hasDownvoted,
            'postedBy' => $formattedAuthor,
            'isAdmin' => $isAdmin
        );
    }

    public function formatUser($user)
    {
        $data = $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
            RETURN  id(u) as id,
                    u.firstName as firstName,
                    u.lastName as lastName,
                    u.image as image
        ', array(
            'userId' => $user
        ));

        if ($data) {
            return $data[0];
        }

        return array(
            'id' => '-1',
            'firstName' => 'MIWI',
            'lastName' => '',
            'image' => 'http://api.miwi.com/img/node/default_img.png'
        );
    }

    public function formatGroup($groupId, $userId)
    {
        $group = $this->sendCypherQuery('
            MATCH   (g:GROUP)
            WHERE   id(g) = {groupId}
            RETURN  id(g) as id,
                    g.title as title,
                    g.image as image,
                    g.description as description,
                    g.website as website,
                    g.members as memberCount
        ', array(
            'groupId' => $groupId
        ));

        $group = $group[0];

        $isMember = count($this->sendCypherQuery('
            MATCH   (u:USER)-[r:MEMBER_OF]->(g:GROUP)
            WHERE   id(u) = {userId}
            AND     id(g) = {groupId}
            RETURN  r
        ', array(
            'groupId' => $groupId,
            'userId' => $userId
        ))) > 0;

        $isAdmin = count($this->sendCypherQuery('
            MATCH   (u:USER)-[r:ADMIN_OF]->(g:GROUP)
            WHERE   id(u) = {userId}
            AND     id(g) = {groupId}
            RETURN  r
        ', array(
            'groupId' => $groupId,
            'userId' => $userId
        ))) > 0;

        return array(
            'id' => $groupId,
            'title' => $group['title'],
            'description' => $group['description'],
            'memberCount' => $group['memberCount'],
            'website' => $group['website'],
            'image' => $group['image'],
            'isMember' => $isMember,
            'isAdmin' => $isAdmin
        );
    }

    public function formatUserWithInterests($user, $userId = null)
    {
        $interests = $this->sendCypherQuery('
            MATCH   (u:USER)-[:LIKES]->(i:INTEREST)
            WHERE   id(u) = {userId}
            RETURN  id(i) as id,
                    i.name as name
        ', array(
            'userId' => $user['id']
        ));

        $isFollowing = false;

        if ($userId) {
            $isFollowing = count($this->sendCypherQuery('
                MATCH   (u:USER)-[:IS_FOLLOWING]->(f:USER)
                WHERE   id(u) = {userId}
                AND     id(f) = {followingId}
                RETURN  u
            ', array(
                'userId' => $userId,
                'followingId' => $user['id']
            ))) > 0;
        }

        return array(
            'id' => $user['id'],
            'firstName' => $user['firstName'],
            'lastName' => $user['lastName'],
            'image' => $user['image'],
            'isFollowing' => $isFollowing,
            'interests' => $interests
        );
    }

    public function formatPerson($item)
    {
        $commonInterests = array();
        $otherInterests = array();

        foreach ($item['commonInterests'] as $interest) {
            $iData = $this->sendCypherQuery('
                MATCH   (i:INTEREST)
                WHERE   id(i) = {interestId}
                RETURN  i.name as name
            ', array(
                'interestId' => $interest
            ));

            $commonInterests[] = array(
                'id' => $interest,
                'name' => $iData[0]['name']
            );
        }

        foreach ($item['otherInterests'] as $interest) {
            $iData = $this->sendCypherQuery('
                MATCH   (i:INTEREST)
                WHERE   id(i) = {interestId}
                RETURN  i.name as name
            ', array(
                'interestId' => $interest
            ));

            $otherInterests[] = array(
                'id' => $interest,
                'name' => $iData[0]['name']
            );
        }

        return array(
            'id' => $item['id'],
            'firstName' => $item['firstName'],
            'lastName' => $item['lastName'],
            'image' => $item['image'],
            'commonInterests' => $commonInterests,
            'otherInterests' => $otherInterests,
            'type' => 'person'
        );
    }
}
