<?php


namespace AppBundle\Service;

use AppBundle\Error\Error;

/**
 * Class InterestManager
 *
 * Handles everything pertaining to interests
 *
 * @package AppBundle\Service
 */
class InterestManager extends BaseManager
{
    public function getInterests($offset, $count)
    {
        if (!is_int($offset) || $offset < 0) {
            throw new \Exception('Invalid offset', 101);
        }

        if (!is_int($count)) {
            return Error::INVALID_COUNT;
        }

        if ($count < 1 || $count > 100) {
            return Error::WRONG_COUNT;
        }

        $interests = $this->sendCypherQuery('
            MATCH   (i:INTEREST)
            RETURN  id(i) as interestId,
                    i.name as interestName
            SKIP    {offset}
            LIMIT   {count}
        ', array(
            'offset' => $offset,
            'count' => $count
        ));

        return $interests;
    }
}