<?php


namespace AppBundle\Service;


use AppBundle\Security\Exception\InvalidInterestException;
use AppBundle\Security\Exception\InvalidLimitException;
use AppBundle\Security\Exception\InvalidOffsetException;

class TimelineManager extends BaseManager
{
    /**
     * Get interest timeline
     *
     * @param int $userId
     * @param int $interestId
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws InvalidInterestException
     * @throws InvalidLimitException
     * @throws InvalidOffsetException
     */
    public function getInterestTimeline($userId, $interestId, $offset, $limit)
    {
        $parameters = array(
            'userId' => $userId,
            'interestId' => $interestId,
            'offset' => $offset,
            'limit' => $limit,
            'peopleOffset' => (int) ceil($offset / $limit),
            'peopleLimit' => 1
        );

        $contentItemsCypherString = sprintf('
            MATCH           (u:USER)-[:LIKES]->(i:INTEREST)<-[:ASSOCIATED_WITH]-(c:CONTENT)
            WHERE           id(u) = {userId}
            %s
            AND NOT         (u)-[:HAS_HIDDEN]->(c)
            WITH            u, c
            OPTIONAL MATCH  (u)-[v:HAS_VOTED]->(c)
            OPTIONAL MATCH  (u)-[f:HAS_FAVORITED]->(c)
            RETURN          id(c)           as id,
                            c.user          as author,
                            c.title         as title,
                            c.body          as body,
                            c.date          as date,
                            c.upvotes       as upvotes,
                            c.downvotes     as downvotes,
                            c.images        as images,
                            c.comments      as commentCount,
                            c.favorites     as favoriteCount,
                            c.interestId    as interestId,
                            c.link          as link,
                            CASE v.score
                                WHEN 1
                                THEN true
                                ELSE false
                            END             as hasUpvoted,
                            CASE v.score
                                WHEN -1
                                THEN true
                                ELSE false
                            END             as hasDownvoted,
                            CASE f
                                WHEN NULL
                                THEN false
                                ELSE true
                            END             as hasFavorited,
                            CASE
                                WHEN id(u) = c.user
                                THEN true
                                ELSE false
                            END             as isAdmin,
                            CASE
                                WHEN "POST" IN labels(c)
                                THEN "post"
                                ELSE "article"
                            END             as type
            ORDER BY        c.score DESC
            SKIP            {offset}
            LIMIT           {limit}
        ', $interestId === 0 ? '' : 'AND id(i) = {interestId}');

        $peopleCypherString = '
            MATCH           (u:USER)-[ui:LIKES]->(i:INTEREST)<-[fi:LIKES]-(f:USER)
            WHERE           id(u) = {userId}
            AND             u <> f
            AND NOT         (u)-[:IS_FOLLOWING]->(f)
            WITH            f, collect(id(i)) as commonInterests
            WHERE           size(commonInterests)> 2
            OPTIONAL MATCH  (f)-[fj:LIKES]->(j:INTEREST)
            WHERE NOT       id(j) IN commonInterests
            RETURN          id(f) as id,
                            f.firstName as firstName,
                            f.lastName as lastName,
                            f.image as image,
                            collect(id(j)) as otherInterests,
                            commonInterests as commonInterests,
                            "person" as type
            ORDER BY        SIZE(commonInterests) DESC, id
            SKIP            {peopleOffset}
            LIMIT           {peopleLimit}
        ';

        $timelineItems = $this->sendCypherQueries(array(
            array(
                'statement' => $contentItemsCypherString,
                'parameters' => $parameters
            ),
            array(
                'statement' => $peopleCypherString,
                'parameters' => $parameters
            )
        ));

        $timelineItems = call_user_func_array('array_merge', $timelineItems);
        $items = array();
        $people = array();

        foreach ($timelineItems as $item) {
            if ($item['type'] === 'post') {
                $item['postedBy'] = $this->container->get('formatter')->formatUser($item['author']);
                $item['hasCommented'] = false;
                $items[] = $item;
            } elseif ($item['type'] === 'person') {
                $people[] = $this->container->get('formatter')->formatPerson($item);
            }
        }

        if (count($items) > 0) {
            array_splice($items, rand(0, $limit), 0, $people);
        }

        return $items;
    }

    /**
     * Upvotes an item for a user
     *
     * In the case the item was already upvoted, the previous upvote will be canceled out,
     * thus lowering the total upvotes by 1. If there was a downvote however, then the item
     * will have its total downvotes lowered by 1 and total upvotes upped by 1, effectively
     * increasing its 'score' by 2.
     *
     * @param int $userId
     * @param int $itemId
     * @return array
     * @throws \Exception
     */
    public function upvoteItem($userId, $itemId)
    {
        $status = $this->getUserToItemStatus($userId, $itemId);
        $hasUpvoted = null;
        $hasDownvoted = null;

        switch ($status) {
            case 0:
                $query = '
                    MATCH   (u:USER)-[ui:HAS_VOTED]->(i)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    SET     ui.score = 1
                    SET     i.upvotes = i.upvotes + 1
                    SET     i.score = i.score + 3600000
                    RETURN  i.upvotes as upvotes,
                            i.downvotes as downvotes,
                            i.user as user,
                            labels(i) as labels
                ';
                $hasUpvoted = true;
                $hasDownvoted = false;
                break;
            case 1:
                $query = '
                    MATCH   (u:USER)-[ui:HAS_VOTED]->(i)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    SET     ui.score = 0
                    SET     i.upvotes = i.upvotes - 1
                    SET     i.score = i.score - 3600000
                    RETURN  i.upvotes as upvotes,
                            i.downvotes as downvotes,
                            i.user as user,
                            labels(i) as labels
                ';
                $hasUpvoted = false;
                $hasDownvoted = false;
                break;
            case 2:
                $query = '
                    MATCH   (u:USER)-[ui:HAS_VOTED]->(i)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    SET     ui.score = 1
                    SET     i.downvotes = i.downvotes - 1
                    SET     i.upvotes = i.upvotes + 1
                    SET     i.score = i.score + 3600000
                    RETURN  i.upvotes as upvotes,
                            i.downvotes as downvotes,
                            i.user as user,
                            labels(i) as labels
                ';
                $hasUpvoted = true;
                $hasDownvoted = false;
                break;
            case 3:
                $query = '
                    MATCH   (u:USER), (i)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    CREATE  (u)-[ui:HAS_VOTED {score: 1}]->(i)
                    SET     i.upvotes = i.upvotes + 1
                    SET     i.score = i.score + 3600000
                    RETURN  i.upvotes as upvotes,
                            i.downvotes as downvotes,
                            i.user as user,
                            labels(i) as labels
                ';
                $hasUpvoted = true;
                $hasDownvoted = false;
                break;
            default:
                throw new \Exception();
        }

        $score = $this->sendCypherQuery($query, array(
            'itemId' => $itemId,
            'userId' => $userId
        ));

        $this->container->get('manager.notification')->sendNotification(
            (int) $score[0]['user'],
            NotificationManager::NOTIFICATION_OBJECT_TYPE_POST,
            NotificationManager::NOTIFICATION_OBJECT_ACTION_UPVOTE,
            $itemId,
            array(
                $userId
            )
        );

        return array(
            'upvotes' => $score[0]['upvotes'],
            'downvotes' => $score[0]['downvotes'],
            'hasUpvoted' => $hasUpvoted,
            'hasDownvoted' => $hasDownvoted
        );
    }

    /**
     * Downvotes an item for a user
     *
     * In the case the item was already downvotes, the previous downvote will be canceled out,
     * thus lowering the total downvotes by 1. If there was a upvote however, then the item
     * will have its total upvotes lowered by 1 and total downvotes upped by 1, effectively
     * decreasing its 'score' by 2.
     *
     * @param int $userId
     * @param int $itemId
     * @return array
     * @throws \Exception
     */
    public function downvoteItem($userId, $itemId)
    {
        $status = $this->getUserToItemStatus($userId, $itemId);
        $hasUpvoted = null;
        $hasDownvoted = null;

        switch ($status) {
            case 0:
                $query = '
                    MATCH   (u:USER)-[ui:HAS_VOTED]->(i)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    SET     ui.score = -1
                    SET     i.downvotes = i.downvotes + 1
                    SET     i.score = i.score - 3600000
                    RETURN  i.downvotes as downvotes,
                            i.upvotes as upvotes,
                            i.user as user,
                            labels(i) as labels,
                ';
                $hasUpvoted = false;
                $hasDownvoted = true;
                break;
            case 1:
                $query = '
                    MATCH   (u:USER)-[ui:HAS_VOTED]->(i)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    SET     ui.score = -1
                    SET     i.downvotes = i.downvotes + 1
                    SET     i.upvotes = i.upvotes - 1
                    SET     i.score = i.score - 3600000
                    RETURN  i.upvotes as upvotes,
                            i.downvotes as downvotes,
                            i.user as user,
                            labels(i) as labels
                ';
                $hasUpvoted = false;
                $hasDownvoted = true;
                break;
            case 2:
                $query = '
                    MATCH   (u:USER)-[ui:HAS_VOTED]->(i,
                            labels(i) as labels)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    SET     ui.score = 0
                    SET     i.downvotes = i.downvotes - 1
                    SET     i.score = i.score + 3600000
                    RETURN  i.upvotes as upvotes,
                            i.downvotes as downvotes,
                            i.user as user,
                            labels(i) as labels
                ';
                $hasUpvoted = false;
                $hasDownvoted = false;
                break;
            case 3:
                $query = '
                    MATCH   (u:USER), (i)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    CREATE  (u)-[ui:HAS_VOTED {score: -1}]->(i)
                    SET     i.downvotes = i.downvotes + 1
                    SET     i.score = i.score - 3600000
                    RETURN  i.downvotes as downvotes,
                            i.upvotes as upvotes,
                            i.user as user,
                            labels(i) as labels
                ';
                $hasUpvoted = false;
                $hasDownvoted = true;
                break;
            default:
                throw new \Exception();
        }

        $score = $this->sendCypherQuery($query, array(
            'itemId' => $itemId,
            'userId' => $userId
        ));

        return array(
            'upvotes' => $score[0]['upvotes'],
            'downvotes' => $score[0]['downvotes'],
            'hasUpvoted' => $hasUpvoted,
            'hasDownvoted' => $hasDownvoted
        );
    }

    /**
     * Gets status of given user to given item
     *
     * Status meanings:
     * 0: Neutral (voted atleast once, but retracted)
     * 1: Upvoted
     * 2: Downvoted
     * 3: No action yet
     *
     * @param int $userId
     * @param int $itemId
     * @return int
     */
    protected function getUserToItemStatus($userId, $itemId)
    {
        $status = $this->sendCypherQuery('
            OPTIONAL MATCH   (u:USER)-[ui:HAS_VOTED]->(i:CONTENT)
            WHERE   id(u) = {userId}
            AND     id(i) = {itemId}
            RETURN  CASE
            WHEN ui.score = 0
            THEN 0
            WHEN ui.score > 0
            THEN 1
            WHEN ui.score < 0
            THEN 2
            ELSE 3
            END AS status
        ', array(
            'userId' => $userId,
            'itemId' => $itemId
        ));

        return $status[0]['status'];
    }

    /**
     * Flag item
     *
     * @param $userId
     * @param $itemId
     * @return bool
     * @throws \Exception
     */
    public function flagItem($userId, $itemId)
    {
        $item = $this->sendCypherQuery('
            MATCH           (u:USER), (i:CONTENT)
            WHERE           id(u) = {userId}
            AND             id(i) = {itemId}
            CREATE UNIQUE   (u)-[:HAS_HIDDEN]->(i)
            RETURN          i.title as title,
                            u.firstName as firstName,
                            u.lastName as lastName,
                            u.email as email
        ', array(
            'userId' => $userId,
            'itemId' => $itemId
        ));

        if ($item) {

            $message = \Swift_Message::newInstance('An item got reported on Vurze');
            $message->setBody($this->templateEngine->render(':mails/item:reported.html.twig', array(
                'user' => array(
                    'firstName' => $item[0]['firstName'],
                    'lastName' => $item[0]['lastName'],
                    'email' => $item[0]['email']
                ),
                'title' => $item[0]['title']
            )));
            $message->setTo(array('info@miwi.com', 'finn@miwi.com', 'jackie@miwi.com'));
            $message->setFrom('info@miwi.com');

            $this->mailer->send($message);

            return true;
        }

        return false;
    }

    public function hideItem($userId, $itemId)
    {
        $item = $this->sendCypherQuery('
            MATCH           (u:USER), (i:CONTENT)
            WHERE           id(u) = {userId}
            AND             id(i) = {itemId}
            CREATE UNIQUE   (u)-[:HAS_HIDDEN]->(i)
            RETURN          i.title as title,
                            u.firstName as firstName,
                            u.lastName as lastName,
                            u.email as email
        ', array(
            'userId' => $userId,
            'itemId' => $itemId
        ));

        if ($item) {
            return true;
        }

        return false;
    }

}