<?php


namespace AppBundle\Service;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * Class ContentManager
 *
 * @package AppBundle\Service
 */
class ContentManager extends BaseManager
{
    /**
     * Upvotes an item for a user
     *
     * In case of an item already being upvoted by the user,
     * the action will be undone. Thus every repeated action will undo
     * the previous one.
     *
     * @ApiDoc(
     *  description="Upvotes an item",
     *  tags={},
     *  section="items"
     * )
     *
     * @param string|int $userId
     * @param string|int $itemId
     * @return array
     * @throws InvalidOptionsException|\Exception
     */
    public function upvoteItem($userId, $itemId)
    {
        if (!is_numeric($userId)) {
            throw new InvalidOptionsException();
        }

        if (!is_numeric($itemId)) {
            throw new InvalidOptionsException();
        }

        $userId = (int) $userId;
        $itemId = (int) $itemId;

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

        return $score;
    }

    /**
     * Downvotes an item for a user
     *
     * In case of an item already being downvoted by the user,
     * the action will be undone. Thus every repeated action will undo
     * the previous one.
     *
     * @param string|int $userId
     * @param string|int $itemId
     * @return array
     * @throws InvalidOptionsException|\Exception
     */
    public function downvoteItem($userId, $itemId)
    {
        if (!is_numeric($userId)) {
            throw new InvalidOptionsException();
        }

        if (!is_numeric($itemId)) {
            throw new InvalidOptionsException();
        }

        $userId = (int) $userId;
        $itemId = (int) $itemId;

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

    public function comment($userId, $itemId, $text)
    {

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
     */
    protected function getUserToItemStatus($userId, $itemId)
    {
        $status = $this->sendCypherQuery('
            MATCH   (u:USER)-[ui:HAS_VOTED]->(i:ITEM)
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