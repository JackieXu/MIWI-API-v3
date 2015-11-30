<?php


namespace AppBundle\Service;

use AppBundle\Security\Exception\InvalidLimitException;
use AppBundle\Security\Exception\InvalidOffsetException;

/**
 * Class InterestManager
 *
 * Handles everything pertaining to interests
 *
 * @package AppBundle\Service
 */
class InterestManager extends BaseManager
{
    /**
     * Gets list of interests
     *
     * @param string $query Search query to use, defaults to an empty string.
     * @param integer $offset Amount of interests to skip, used for pagination.
     * @param integer $limit Amount of interests to return at most.
     * @param boolean $defaultOnly If set, only return default interests.
     * @return array List of interests found.
     * @throws InvalidLimitException
     * @throws InvalidOffsetException
     * @throws \Exception
     */
    public function getInterests($query, $offset, $limit, $defaultOnly)
    {
        if (!is_numeric($offset)) {
            throw new InvalidOffsetException();
        }
        if (!is_numeric($limit)) {
            throw new InvalidLimitException();
        }

        $offset = (int) $offset;
        $limit = (int) $limit;

        if ($offset < 0) {
            throw new InvalidOffsetException();
        }

        if ($limit < 1 || $limit > 100) {
            throw new InvalidLimitException();
        }

        if ($defaultOnly) {
            $cypherString = '
                MATCH   (i:INTEREST)
                WHERE   i.name =~ {query}
                AND     i.isDefault = true
                RETURN  id(i) as id,
                        i.name as name,
                        i.image as image
                SKIP    {offset}
                LIMIT   {count}
            ';
        } else {
            $cypherString = '
                MATCH   (i:INTEREST)
                WHERE   i.name =~ {query}
                RETURN  id(i) as id,
                        i.name as name
                SKIP    {offset}
                LIMIT   {count}
            ';
        }

        $interests = $this->sendCypherQuery($cypherString, array(
            'query' => '(?i)'.$query.'.*',
            'offset' => $offset,
            'count' => $limit
        ));

        $interestResults = array();
        foreach ($interests as $interest) {
            $interestResults[] = array(
                'id' => $interest['id'],
                'name' => ucfirst($interest['name'])
            );
        }

        return $interestResults;
    }

    /**
     * Add interests to user
     *
     * Interests that are already in the user's list will be ignored.
     *
     * @param int $userId
     * @param string[] $interestNames
     * @return array The entire list of interests of the user
     */
    public function addInterests($userId, array $interestNames)
    {
        $interestNames = array_values($interestNames);
        $interestNamesSanitized = array();

        foreach ($interestNames as $interestName) {
            $interestNamesSanitized[] = trim(strtolower($interestName));
        }

        $interests = $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
            FOREACH (interestName IN {interestNames} | MERGE (:INTEREST {name: interestName}))
            WITH    u
            MATCH   (i:INTEREST)
            WHERE   i.name IN {interestNames}
            MERGE   (u)-[r:LIKES]->(i)
            RETURN  id(i) as id,
                    i.name as name
        ', array(
            'userId' => $userId,
            'interestNames' => $interestNamesSanitized
        ));

        return $interests;
    }

    /**
     * Share interests with others
     *
     * $data should be in the following format:
     *  array(
     *   interestId => emails
     *  )
     *
     * Where `interestId` is an integer and `emails` is an array of strings.
     *
     * @param array $data
     * @return array List of failures, empty if everything ran without issues.
     */
    public function shareInterests(array $data)
    {
//        $mailer = $this->container->get('mailer');
        $uniqueInterestIds = array_keys($data);
        $failedMails = array();

        $interests = $this->sendCypherQuery('
            MATCH   (i:INTEREST)
            WHERE   id(i) IN {interestIds}
            RETURN  id(i) as id,
                    i.name as name
        ', array(
            'interestIds' => $uniqueInterestIds
        ));

        // Reformat interests array into the `id => id, name` form
        $interests = array_reduce($interests, function ($result, $interest) {
            $result[$interest['id']] = $interest;
        }, array());

        $emailInterests = array();

        foreach ($data as $interestId => $emailAddresses) {
            $emailInterests[$interestId] = array(
                'interest' => array(
                    'id' => $interestId,
                    'name' => $interests[$interestId]['name']
                ),
                'emailAddresses' => array()
            );

            foreach ($emailAddresses as $emailAddress) {
                $emailInterests[$interestId]['emailAddresses'][] = $emailAddress;
            }
        }

        return $failedMails;
    }

    /**
     * Gets user interests
     *
     * @param int $profileId
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public function getUserInterests($profileId, $userId)
    {
        $cypherString = '
            MATCH   (u:USER)-[ui:LIKES]->(i:INTEREST)
            WHERE   id(u) = {userId}
            RETURN  id(i) as id,
                    i.name as name
        ';

        $interests = $this->sendCypherQuery($cypherString, array(
            'userId' => $profileId
        ));

        $iRes = array();

        foreach ($interests as $interest) {
            $hasInterest = $this->sendCypherQuery('
                MATCH   (u:USER)-[ui:LIKES]->(i:INTEREST)
                WHERE   id(u) = {userId}
                AND     id(i) = {interestId}
                RETURN  count(ui) as c
            ', array(
                'userId' => $userId,
                'interestId' =>  $interest['id']
            ));
            $iRes[] = array(
                'id' => $interest['id'],
                'name' => $interest['name'],
                'hasInterest' => $hasInterest[0]['c'] === 1
            );
        }

        return $iRes;
    }

    /**
     * Gets user top interests
     *
     * @param int $userId
     * @return array|bool
     */
    public function getUserTopInterests($userId)
    {
        $interests = $this->sendCypherQuery('
            MATCH       (u:USER)-[r:LIKES]->(i:INTEREST)
            WHERE       id(u) = {userId}
            RETURN      id(i) as id,
                        i.name as name,
                        r.measure as measure,
                        r.order as order
            ORDER BY    r.order
            LIMIT       5
        ', array(
            'userId' => $userId
        ));

        if ($interests) {
            $results = array();
            $interestCount = count($interests);

            for ($i = 0; $i < count($interests); $i++) {
                $results[] = array(
                    'id' => $interests[$i]['id'],
                    'name' => $interests[$i]['name'],
                    'measure' => 100 / $interestCount,
                    'order' => $i + 1
                );
            }

            return $results;
        }

        return false;
    }

    /**
     * Create interest
     *
     * @param $name
     * @return array|bool
     * @throws \Exception
     */
    public function createInterest($name)
    {
        $interest = $this->sendCypherQuery('
            MERGE   (i:INTEREST {name: {name}})
            RETURN  id(i) as id,
                    i.name as name
        ', array(
            'name' => $name
        ));

        if ($interest) {
            return $interest[0];
        }

        return false;
    }

    /**
     * Add interest to user
     *
     * @param int $userId
     * @param string $interestName
     * @return bool
     * @throws \Exception
     */
    public function addInterest($userId, $interestName)
    {
        $interest = $this->sendCypherQuery('
            MERGE   (i:INTEREST {name: {name}})
            WITH    i
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
            MERGE   (u)-[r:LIKES]->(i)
            RETURN  id(i) as id,
                    i.name as name
        ', array(
            'userId' => $userId,
            'name' => $interestName
        ));

        if ($interest) {
            return $interest[0];
        }

        return false;
    }

    public function getMainInterests($limit)
    {
        $interests = $this->sendCypherQuery('
            MATCH       (i:INTEREST)<-[r:ASSOCIATED_WITH]-(p:POST)
            WITH        i, count(r) as postCount
            RETURN      id(i) as id,
                        i.name as name
            ORDER BY    postCount DESC
            LIMIT       {limit}
        ', array(
            'limit' => $limit
        ));

        return $interests;
    }
}
