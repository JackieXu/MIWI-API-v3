<?php


namespace AppBundle\Service;


class GroupManager extends BaseManager
{
    /**
     * Gets groups associated with interest
     *
     * @param int $interestId
     * @return array
     */
    public function getInterestGroups($interestId)
    {
        $groups = $this->sendCypherQuery('
            MATCH   (g:GROUP)-[:ASSOCIATED_WITH]->(i:INTEREST)
            WHERE   id(i) = {interestId}
            RETURN  id(g) as id,
                    g.title as title,
                    g.image as image
        ', array(
            'interestId' => $interestId
        ));

        return $groups;
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
            RETURN  id(g) as id,
                    g.title as title,
                    g.image as image,
                    g.body as description
        ', array(
            'groupId' => $groupId
        ));

        if ($group) {
            return $group[0];
        }

        return false;
    }
}