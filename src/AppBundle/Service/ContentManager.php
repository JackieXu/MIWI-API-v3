<?php


namespace AppBundle\Service;

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

    /**
     * @param int $userId
     * @param int $itemId
     * @param string $text
     * @return bool
     * @throws \Exception
     */
    public function comment($userId, $itemId, $text)
    {
        $commentId = $this->sendCypherQuery('
            MATCH   (u:USER), (i:ITEM)
            WHERE   id(u) = {userId}
            AND     id(i) = {itemId}
            WITH    i, u
            CREATE  (u)-[uc:HAS_COMMENTED]->(c:COMMENT {
                text: {text}
            })-[ci:COMMENT_ON]->(i)
            RETURN  id(c) as id
        ', array(
            'itemId' => $itemId,
            'userId' => $userId,
            'text' => $text
        ));

        if ($commentId) {
            return $commentId[0]['id'];
        }

        return false;
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

    public function get($itemId, $userId)
    {
        $item = $this->sendCypherQuery('
            MATCH   (i:ITEM)
            WHERE   id(i) = {itemId}
            RETURN  id(i) as id,
                    i.title as title,
                    i.body as body,
                    i.images as images,
                    i.link as link,
                    i.upvotes as upvotes,
                    i.downvotes as downvotes,
                    i.comments as comments
        ', array(
            'itemId' => $itemId
        ));

        if ($item) {
            return $item[0];
        }

        return false;
    }

    /***
     * @param $title
     * @param $body
     * @param $images
     * @param $userId
     * @param $interestId
     * @return bool
     * @throws \Exception
     */
    public function create($title, $body, $images, $userId, $interestId)
    {
        $images = $this->processImages($images);

        if ($interestId === 0) {
            $itemId = $this->sendCypherQuery('
                MATCH   (u:USER)
                WHERE   id(u) = {userId}
                WITH    u
                CREATE  (u)-[:HAS_POSTED]->(i:ITEM:CONTENT {
                    title: {title}
                    body: {body}
                    images: {images}
                })
                RETURN id(i) as id
            ', array(
                'title' => $title,
                'body' => $body,
                'images' => $images,
                'userId' => $userId
            ));
        } else {
            $itemId = $this->sendCypherQuery('
                MATCH   (u:USER), (i:INTEREST)
                WHERE   id(u) = {userId}
                AND     id(i) = {interestId}
                WITH    u, i
                CREATE  (u)-[:HAS_POSTED]->(c:ITEM:CONTENT {
                    title: {title}
                    body: {body}
                    images: {images}
                })-[:ASSOCIATED_WITH]->(i)
                RETURN id(c) as id
            ', array(
                'title' => $title,
                'body' => $body,
                'images' => $images,
                'userId' => $userId,
                'interestId' => $interestId
            ));
        }

        if ($itemId) {
            return $itemId[0]['id'];
        }

        return false;
    }

    private function processImages($images)
    {
        $templateString = '%s/img/node/%s';
        $saveRoot = '/var/www/av3/web';
        $webRoot = 'http://av3.miwi.com';
        $fileName = uniqid();

        $files = array();

        foreach ($images as $string) {
            if (filter_var($string, FILTER_VALIDATE_URL) !== false) {
                return $string;
            }

            if (strpos($string, ',')) {

                $data = explode(',', $string);
                $image = base64_decode($data[1]);

            } else {

                $image = base64_decode($string);

            }

            $file = finfo_open();
            $mimeType = finfo_buffer($file, $image, FILEINFO_MIME_TYPE);
            finfo_close($file);

            $mimeArray = explode('/', $mimeType);
            $extension = array_pop($mimeArray);

            $saveLocation = sprintf($templateString, $saveRoot, $fileName . '_orig.' . $extension);
            $webLocation = sprintf($templateString, $webRoot, $fileName . '_orig.' . $extension);

            $files[] = $this->saveData($saveLocation, $webLocation, $image);
        }

        return $files;
    }

    private function saveData($saveLocation, $webLocation, $content)
    {
        $temp = tempnam(sys_get_temp_dir(), 'temp');

        if (!($f = @fopen($temp, 'wb'))) {
            $temp = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('temp');
            if (!($f = @fopen($temp, 'wb'))) {
                trigger_error(sprintf('Error writing temp file `%s`', $temp), E_USER_WARNING);
                return false;
            }
        }

        @fwrite($f, $content);
        @fclose($f);

        if (!@rename($temp, $saveLocation)) {
            @unlink($saveLocation);
            @rename($temp, $saveLocation);
        }

        @chmod($saveLocation, 0777);

        return $webLocation;
    }
}
