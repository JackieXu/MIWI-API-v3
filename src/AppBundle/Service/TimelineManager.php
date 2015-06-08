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
                        c.title as title,
                        c.body as body,
                        c.visibility as visibility,
                        c.likes as likes,
                        split(c.images, ",") as images,
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
}