<?php


namespace AppBundle\Service;

/**
 * Class GroupManager
 *
 * @package AppBundle\Service
 */
class GroupManager extends BaseManager
{
    /**
     * Gets groups associated with interest
     *
     * @param int $interestId
     * @param int $userId
     * @return array
     */
    public function getInterestGroups($interestId, $userId)
    {
        $groups = $this->sendCypherQuery('
            MATCH   (u:USER)-[:MEMBER_OF]->(g:GROUP)

        ', array(

        ));

        $suggestedGroups = $this->sendCypherQuery('
            MATCH   (g:GROUP)-[:ASSOCIATED_WITH]->(i:INTEREST)
            WHERE   id(i) = {interestId}
            RETURN  id(g) as id,
                    g.title as title,
                    g.image as image
        ', array(
            'interestId' => $interestId
        ));

        $groupData = array(
            'groups' => $groups,
            'suggested' => $suggestedGroups
        );

        return $groupData;
    }

    /**
     * Get group info
     *
     * @param int $groupId
     * @param int $userId
     * @return array|bool
     */
    public function getGroup($groupId, $userId)
    {
        $group = $this->sendCypherQuery('
            MATCH   (g:GROUP)
            WHERE   id(g) = {groupId}
            RETURN  id(g) as id
        ', array(
            'groupId' => $groupId
        ));

        if ($group) {
            return $this->container->get('formatter')->formatGroup($groupId, $userId);
        }

        return false;
    }

    /**
     * Join group
     *
     * @param int $groupId
     * @param int $userId
     * @return array|bool
     * @throws \Exception
     */
    public function joinGroup($groupId, $userId)
    {
        $group = $this->sendCypherQuery('
            MATCH   (u:USER), (g:GROUP)
            WHERE   id(u) = {userId}
            AND     id(g) = {groupId}
            MERGE   (u)-[r:MEMBER_OF]->(g)
            ON CREATE SET r.joinDate = timestamp()
            RETURN  r.joinDate
        ', array(
            'userId' => $userId,
            'groupId' => $groupId
        ));

        if ($group) {
            return $group[0];
        }

        return false;
    }

    /**
     * @param int $groupId
     * @param int $userId
     * @return array|bool
     * @throws \Exception
     */
    public function leaveGroup($groupId, $userId)
    {
        $group = $this->sendCypherQuery('
            MATCH   (u:USER), (g:GROUP)
            WHERE   id(u) = {userId}
            AND     id(g) = {groupId}
        ', array(
            'userId' => $userId,
            'groupId' => $groupId
        ));

        if ($group) {
            return $group[0];
        }

        return false;
    }

    public function createGroup($title, $description, $website, $visibility, $interestId, $userId)
    {
        $group = $this->sendCypherQuery('
            MATCH   (g:GROUP)-[:ASSOCIATED_WITH]->(i:INTEREST)
            WHERE   g.title = {title}
            AND     id(i) = {interestId}
            RETURN  id(g)
        ', array(
            'title' => $title,
            'interestId' => $interestId
        ));

        if ($group) {
            return false;
        }

        $group = $this->sendCypherQuery('
            MATCH   (u:USER), (i:INTEREST)
            WHERE   id(u) = {userId}
            AND     id(i) = {interestId}
            WITH    u, i
            CREATE  (u)-[:ADMIN_OF {date: {date}}]->(g:GROUP {
                title: {title},
                description: {description},
                website: {website},
                members: 1,
                interestId: {interestId},
                visibility: {visibility}
            })-[:ASSOCIATED_WITH]->(i)
            WITH    u, g
            CREATE  (u)-[:MEMBER_OF {date: {date}}]->(g)
            RETURN  id(g) as id
        ', array(
            'title' => $title,
            'description' => $description,
            'website' => $website,
            'visibility' => $visibility,
            'userId' => $userId,
            'interestId' => $interestId
        ));

        if ($group) {
            return $group[0];
        }

        return false;
    }

    /**
     * @param int $groupId
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @param string $query
     * @return array
     * @throws \Exception
     */
    public function getMembers($groupId, $userId, $limit, $offset, $query)
    {
        $users = $this->sendCypherQuery('
            MATCH   (u:USER)-[:MEMBER_OF]->(g:GROUP)
            WHERE   id(g) = {groupId}
            AND     u.name =~ {query}
            RETURN  id(u) as id,
                    u.firstName as firstName,
                    u.lastName as lastName,
                    u.image as image
            SKIP    {offset}
            LIMIT   {limit}
        ', array(
            'groupId' => $groupId,
            'limit' => $limit,
            'offset' => $offset,
            'query' => '(?i)'.$query.'.*',
        ));

        $userData = array();

        foreach ($users as $user) {
            $userData[] = $this->container->get('formatter')->formatUserWithInterests($user, $userId);
        }

        return $userData;
    }

    /**
     * Get events
     *
     * @param int $groupId
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @param string $query
     * @return array
     */
    public function getEvents($groupId, $userId, $limit, $offset, $query)
    {
        $events = $this->sendCypherQuery('
            MATCH   (e:EVENT)-[:ASSOCIATED_WITH]->(g:GROUP)
            WHERE   id(g) = {groupId}
            AND     e.title =~ {query}
            RETURN  id(e) as id,
                    e.title as title,
                    e.image as image,
                    SUBSTRING(e.description, 0, 200) as description,
                    e.members as memberCount,
                    e.startDate as startDate,
                    e.endDate as endDate
            SKIP    {offset}
            LIMIT   {limit}
        ', array(
            'groupId' => $groupId,
            'offset' => $offset,
            'limit' => $limit,
            'query' => '(?i)'.$query.'.*'
        ));

        return $events;
    }
}