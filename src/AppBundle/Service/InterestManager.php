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
                RETURN  id(i) as interestId,
                        i.name as interestName
                SKIP    {offset}
                LIMIT   {count}
            ';
        } else {
            $cypherString = '
                MATCH   (i:INTEREST)
                WHERE   i.name =~ {query}
                RETURN  id(i) as interestId,
                        i.name as interestName
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
     * @param int[] $interestIds
     * @return int[] The entire list of interests of the user
     */
    public function addInterests($userId, array $interestIds)
    {
        $interests = $this->sendCypherQuery('
            MATCH   (u:USER),
                    (i:INTEREST)
            WHERE   id(u) = {userId}
            AND     id(i) IN {interestIds}
            MERGE   (u)-[r:LIKES]->(i)
            RETURN  id(i) as id
        ', array(
            'userId' => $userId,
            'interestIds' => $interestIds
        ));

        return array_values($interests);
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
        $mailer = $this->container->get('mailer');
        $uniqueInterestIds = array();
        $failedMails = array();

        foreach ($data as $email => $interestIds) {
            foreach ($interestIds as $interestId) {
                if (!in_array($interestId, $uniqueInterestIds)) {
                    $uniqueInterestIds[] = $interestId;
                }
            }
        }

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

        foreach ($data as $email => $interestIds) {
            $emailInterests = array();

            foreach ($interestIds as $interestId) {
                $emailInterests[] = $interests[$interestId];
            }

            $message = \Swift_Message::newInstance();
            $message->setSubject('');
            $message->setFrom('info@miwi.com', 'MIWI');
            $message->setTo($email);
            $message->setBody('', 'text/html', 'UTF-8');

            $mailer->send($message, $failedMails);
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
            AND     ui.status = {status}
            RETURN  id(i) as interestId,
                    i.name as interestName
        ';

        $interests = $this->sendCypherQuery($cypherString, array(
            'userId' => $userId,
            'status' => 1
        ));

        return $interests;
    }
}
