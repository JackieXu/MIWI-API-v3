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

        return $interests;
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
            'interestNames' => array_values($interestNames)
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
     * @param int $userId
     * @return array
     */
    public function getUserInterests($userId)
    {
        $cypherString = '
            MATCH   (u:USER)-[ui:LIKES]->(i:INTEREST)
            WHERE   id(u) = {userId}
            RETURN  id(i) as id,
                    i.name as name
        ';

        $interests = $this->sendCypherQuery($cypherString, array(
            'userId' => $userId
        ));

        return $interests;
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
     * @param int $interestId
     * @param string $visibility
     * @return bool
     * @throws \Exception
     */
    public function addInterest($userId, $interestId, $visibility)
    {
        $interest = $this->sendCypherQuery('
            MATCH   (i:INTEREST), (u:USER)
            WHERE   id(i) = {interestId}
            AND     id(u) = {userId}
            MERGE   (u)-[r:LIKES]->(i)
            SET     r.visibility = {visibility}
            RETURN  id(i) as id,
                    i.name as name
        ', array(
            'userId' => $userId,
            'interestId' => $interestId,
            'visibility' => $visibility
        ));

        if ($interest) {
            return $interest[0];
        }

        return false;
    }
}
