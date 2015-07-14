<?php


namespace AppBundle\Service;


class EventManager extends BaseManager
{
    /**
     * Gets events associated with interest
     *
     * @param int $interestId
     * @return array
     */
    public function getInterestEvents($interestId)
    {
        $events = $this->sendCypherQuery('
            MATCH   (e:EVENT)-[:ASSOCIATED_WITH]->(i:INTEREST)
            WHERE   id(i) = {interestId}
            RETURN  id(e) as id,
                    e.title as title,
                    e.image as image
        ', array(
            'interestId' => $interestId
        ));

        return $events;
    }
}