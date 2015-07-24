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
     * @param integer $userId
     * @param integer $interestId
     * @param integer $offset
     * @param integer $limit
     * @return array
     * @throws InvalidInterestException
     * @throws InvalidLimitException
     * @throws InvalidOffsetException
     */
    public function getInterestTimeline($userId, $interestId, $offset, $limit)
    {
        if (!is_int($interestId)) {
            throw new InvalidInterestException();
        }

        if (!is_int($offset) || $offset < 0) {
            throw new InvalidOffsetException();
        }

        if (!is_int($limit) || $limit < 1 || $limit > 100) {
            throw new InvalidLimitException();
        }

        $parameters = array(
            'userId' => $userId,
            'interestId' => $interestId,
            'offset' => $offset,
            'limit' => $limit
        );

        $contentItemsCypherString = '
            MATCH       (c:CONTENT)-[ci:ASSOCIATED_WITH]->(i:INTEREST), (u:USER)
            WHERE       id(i) = {interestId}
            AND         id(u) = {userId}
            AND NOT     (u)-[:HAS_HIDDEN]->(c)
            RETURN      id(c) as id,
                        c.user as author,
                        c.title as title,
                        c.body as body,
                        c.visibility as visibility,
                        c.likes as upvotes,
                        c.likes as downvotes,
                        c.images as images,
                        c.shares as shares,
                        c.comments as comments,
                        c.date as date
            ORDER BY    c.date DESC
            SKIP        {offset}
            LIMIT       {limit}
        ';

        $groupsCypherString = '
            MATCH       (g:GROUP)-[gi:ASSOCIATED_WITH]->(i:INTEREST), (u:USER)
            WHERE       id(i) = {interestId}
            AND         id(u) = {userId}
            RETURN      id(g) as id,
                        g.title as title,
                        g.body as body,
                        g.user as admin,
                        g.website as website,
                        g.createdOn as creationDate,
                        g.updatedOn as updateDate,
                        g.members as members
            ORDER BY    g.creation_date
            SKIP        {offset}
            LIMIT       1
        ';

        $eventsCypherString = '
            MATCH       (e:EVENT)-[ei:ASSOCIATED_WITH]->(i:INTEREST), (u:USER)
            WHERE       id(i) = {interestId}
            AND         id(u) = {userId}
            RETURN      id(e) as id,
                        e.title as title,
                        e.body as body,
                        e.website as website,
                        e.location as location,
                        e.startDate as startDate,
                        e.endDate as endDate,
                        e.members as members
            ORDER BY    e.creation_date
            SKIP        {offset}
            LIMIT       1
        ';

        $peopleCypherString = '
            MATCH           (u:USER)-[ui:LIKES]->(i:INTEREST)<-[fi:LIKES]-(f:USER)
            WHERE           id(u) = {userId}
            AND             u <> f
            WITH            f,
                            collect(id(i)) as commonInterests,
                            count(i) as commonInterestCount
            WHERE           commonInterestCount > 2
            OPTIONAL MATCH  (f)-[fj:LIKES]->(j:INTEREST)
            WHERE NOT       id(j) IN commonInterests
            AND             fj.type = "active"
            RETURN          id(f) as id,
                            f.username as name,
                            f.image as image,
                            collect(id(j)) as otherInterests,
                            commonInterests
        ';

        $timelineItems = $this->sendCypherQueries(array(
            array(
                'statement' => $contentItemsCypherString,
                'parameters' => $parameters
            ),
            array(
                'statement' => $groupsCypherString,
                'parameters' => $parameters
            ),
            array(
                'statement' => $eventsCypherString,
                'parameters' => $parameters
            ),
            array(
                'statement' => $peopleCypherString,
                'parameters' => $parameters
            )
        ));

        $timelineItems = call_user_func_array('array_merge', $timelineItems);

        return $timelineItems;
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

        switch ($status) {
            case 0:
                $query = '
                    MATCH   (u:USER)-[ui:HAS_VOTED]->(i:ITEM)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    SET     ui.score = 1
                    SET     i.upvotes = i.upvotes + 1
                    RETURN  i.upvotes as upvotes
                ';
                break;
            case 1:
                $query = '
                    MATCH   (u:USER)-[ui:HAS_VOTED]->(i:ITEM)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    SET     ui.score = 0
                    SET     i.upvotes = i.upvotes - 1
                    RETURN  i.upvotes as upvotes
                ';
                break;
            case 2:
                $query = '
                    MATCH   (u:USER)-[ui:HAS_VOTED]->(i:ITEM)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    SET     ui.score = 1
                    SET     i.downvotes = i.downvotes - 1
                    SET     i.upvotes = i.upvotes + 1
                    RETURN  i.upvotes as upvotes,
                            i.downvotes as downvotes
                ';
                break;
            case 3:
                $query = '
                    MATCH   (u:USER), (i:ITEM)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    CREATE  (u)-[ui:HAS_VOTED {score: 1}]->(i)
                    SET     i.upvotes = i.upvotes + 1
                    RETURN  i.upvotes as upvotes
                ';
                break;
            default:
                throw new \Exception();
        }

        $score = $this->sendCypherQuery($query, array(
            'itemId' => $itemId,
            'userId' => $userId
        ));

        return $score[0];
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

        switch ($status) {
            case 0:
                $query = '
                    MATCH   (u:USER)-[ui:HAS_VOTED]->(i:ITEM)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    SET     ui.score = -1
                    SET     i.downvotes = i.downvotes + 1
                    RETURN  i.downvotes as downvotes
                ';
                break;
            case 1:
                $query = '
                    MATCH   (u:USER)-[ui:HAS_VOTED]->(i:ITEM)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    SET     ui.score = -1
                    SET     i.downvotes = i.downvotes + 1
                    SET     i.upvotes = i.upvotes - 1
                    RETURN  i.upvotes as upvotes,
                            i.downvotes as downvotes
                ';
                break;
            case 2:
                $query = '
                    MATCH   (u:USER)-[ui:HAS_VOTED]->(i:ITEM)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    SET     ui.score = 0
                    SET     i.downvotes = i.downvotes - 1
                    RETURN  i.upvotes as upvotes
                ';
                break;
            case 3:
                $query = '
                    MATCH   (u:USER), (i:ITEM)
                    WHERE   id(u) = {userId}
                    AND     id(i) = {itemId}
                    CREATE  (u)-[ui:HAS_VOTED {score: -1}]->(i)
                    SET     i.downvotes = i.downvotes + 1
                    RETURN  i.downvotes as downvotes
                ';
                break;
            default:
                throw new \Exception();
        }

        $score = $this->sendCypherQuery($query, array(
            'itemId' => $itemId,
            'userId' => $userId
        ));

        return $score[0];
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
            OPTIONAL MATCH   (u:USER)-[ui:HAS_VOTED]->(i:ITEM)
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
}