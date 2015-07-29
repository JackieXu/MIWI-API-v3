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
        $formattedAuthor = $this->formatUser($content['user']);
        $hasCommented = false;
        $isAdmin = $content['user'] === $userId;

        $isFavorited = count($this->sendCypherQuery('
            MATCH   (u:USER)-[r:HAS_FAVORITED]->(c:CONTENT)
            WHERE   id(u) = {userId}
            AND     id(c) = {contentId}
            RETURN  r
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


        return array(
            'id' => $content['id'],
            'date' => $content['date'],
            'type' => in_array('ARTICLE', $content['labels']) ? 'article' : 'post',
            'body' => $content['body'],
            'interestId' => $content['interestId'],
            'images' => $images,
            "link" => $content['link'],
            'title' => $content['title'],
            'commentCount' => $content['comments'],
            'hasCommented' => $hasCommented,
            'hasFavorited' => $isFavorited,
            'postedBy' => $formattedAuthor,
            'isAdmin' => $isAdmin
        );
    }

    private function formatUser($user)
    {
        return array();
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
}
