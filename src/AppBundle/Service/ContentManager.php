<?php


namespace AppBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * Class ContentManager
 *
 * @package AppBundle\Service
 */
class ContentManager extends BaseManager
{
    public function search($query, $userId, $interestId, $offset, $limit)
    {
        if ($interestId === 0) {
            $items = $this->sendCypherQuery('
                MATCH       (c:CONTENT), (u:USER)
                WHERE       id(u) = {userId}
                AND NOT     (u)-[:HAS_HIDDEN]->(c)
                AND         c.body =~ {query}
                RETURN      id(c) as id,
                            c.user as author,
                            c.title as title,
                            c.body as body,
                            c.date as date,
                            c.visibility as visibility,
                            c.upvotes as upvotes,
                            c.downvotes as downvotes,
                            c.images as images,
                            c.shares as shares,
                            c.comments as comments,
                            c.favorites as favorites,
                            "content" as type,
                            labels(c) as labels,
                            c.interestId as interestId,
                            c.link as link
                ORDER BY    c.date DESC
                SKIP        {offset}
                LIMIT       {limit}
            ', array(
                'userId' => $userId,
                'interestId' => $interestId,
                'query' => '.*'.$query.'.*',
                'offset' => $offset,
                'limit' => $limit
            ));
        } else {
            $items = $this->sendCypherQuery('
                MATCH       (c:CONTENT)-[:ASSOCIATED_WITH]->(i:INTEREST), (u:USER)
                WHERE       id(u) = {userId}
                AND         id(i) = {interestId}
                AND NOT     (u)-[:HAS_HIDDEN]->(c)
                AND         c.body =~ {query}
                RETURN      id(c) as id,
                            c.user as author,
                            c.title as title,
                            c.body as body,
                            c.date as date,
                            c.visibility as visibility,
                            c.upvotes as upvotes,
                            c.downvotes as downvotes,
                            c.images as images,
                            c.shares as shares,
                            c.comments as comments,
                            c.favorites as favorites,
                            "content" as type,
                            labels(c) as labels,
                            c.interestId as interestId,
                            c.link as link
                ORDER BY    c.date DESC
                SKIP        {offset}
                LIMIT       {limit}
            ', array(
                'userId' => $userId,
                'interestId' => $interestId,
                'query' => '.*'.$query.'.*',
                'offset' => $offset,
                'limit' => $limit
            ));
        }

        $data = array();

        foreach ($items as $item) {
            $data[] = $this->container->get('formatter')->formatContent($item, $userId);
        }

        return $data;
    }

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
            SET     i.comments = i.comments + 1
            WITH    i, u
            CREATE  (u)-[uc:HAS_COMMENTED]->(c:COMMENT {
                text: {text},
                upvotes: 0,
                downvotes: 0,
                date: {date},
                user: {userId}
            })-[ci:COMMENT_ON]->(i)
            RETURN  id(c) as id,
                    c.text as text,
                    c.upvotes as upvotes,
                    c.downvotes as downvotes
        ', array(
            'itemId' => $itemId,
            'userId' => $userId,
            'text' => $text,
            'date' => time()
        ));

        if ($commentId) {
            $userData = $this->sendCypherQuery('
                MATCH   (u:USER)
                WHERE   id(u) = {userId}
                RETURN  id(u) as id,
                        u.firstName as firstName,
                        u.lastName as lastName,
                        u.image as image
            ', array(
                'userId' => $userId
            ));

            return array(
                'id' => $commentId[0]['id'],
                'comment' => $commentId[0]['text'],
                'date' => $commentId[0]['id'],
                'createdBy' => $userData[0]
            );
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
            MATCH   (i:ITEM)-[:ASSOCIATED_WITH]->(n:INTEREST)
            WHERE   id(i) = {itemId}
            WITH    i,n
            MATCH   (u:USER)
            WHERE   id(u) = i.user
            WITH    i,n,u
            RETURN  id(i) as id,
                    i.title as title,
                    i.body as body,
                    i.images as images,
                    i.link as link,
                    i.user as author,
                    i.date as date,
                    i.upvotes as upvotes,
                    i.downvotes as downvotes,
                    i.comments as comments,
                    id(n) as interestId,
                    n.name as interestName,
                    id(u) as userId,
                    u.firstName as userFirstName,
                    u.lastName as userLastName,
                    u.image as userImage,
                    labels(i) as labels
        ', array(
            'itemId' => $itemId
        ));

        if ($item) {
            return $this->container->get('formatter')->formatContent($item[0], $userId);
        }

        return false;
    }


    public function getComments($itemId, $userId, $offset = 0, $limit = 30)
    {
        $comments = $this->sendCypherQuery('
            MATCH   (c:COMMENT)-[:COMMENT_ON]->(i:ITEM)
            WHERE   id(i) = {itemId}
            RETURN  c.text as text,
                    c.user as user,
                    c.date as date,
                    c.upvotes as upvotes,
                    c.downvotes as downvotes
            SKIP    {offset}
            LIMIT   {limit}
        ', array(
            'itemId' => $itemId,
            'offset' => $offset,
            'limit' => $limit
        ));

        $res = array();

        foreach ($comments as $comment) {
            $data = array(
                'comment' => $comment['text'],
                'date' => $comment['date'],
                'upvotes' => $comment['upvotes'],
                'downvotes' => $comment['downvotes'],
                'createdBy' => array()
            );

            $user = $this->sendCypherQuery('
                MATCH   (u:USER)
                WHERE   id(u) = {userId}
                RETURN  id(u) as id,
                        u.firstName as firstName,
                        u.lastName as lastName,
                        u.image as image
            ', array(
                'userId' => $comment['user']
            ));

            if ($user) {
                $data['createdBy'] = $user[0];
            } else {
                $data['createdBy'] = array(
                    'id' => -1,
                    'firstName' => 'Vurze',
                    'lastName' => '',
                    'image' => 'http://api.miwi.com/img/node/default_img.png'
                );
            }

            $res[] = $data;
        }

        return $res;
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
        $imagesRes = $this->processImages($images);

        if ($interestId === 0) {
            $itemId = $this->sendCypherQuery('
                MATCH   (u:USER)
                WHERE   id(u) = {userId}
                WITH    u
                CREATE  (u)-[:HAS_POSTED]->(D:ITEM:CONTENT {
                    title: {title},
                    body: {body},
                    images: {images},
                    user: {userId},
                    date: {date},
                    upvotes: 0,
                    downvotes: 0,
                    comments: 0,
                    favorites: 0
                })
                RETURN  id(d) as id,
                        d.title as title,
                        d.body as body,
                        d.images as images,
                        d.date as date,
                        d.upvotes as upvotes,
                        d.downvotes as downvotes,
                        d.comments as comments
            ', array(
                'title' => $title,
                'body' => $body,
                'images' => $imagesRes,
                'userId' => $userId,
                'date' => time()
            ));
        } else {
            $itemId = $this->sendCypherQuery('
                MATCH   (u:USER), (i:INTEREST)
                WHERE   id(u) = {userId}
                AND     id(i) = {interestId}
                WITH    u, i
                CREATE  (u)-[:HAS_POSTED]->(d:ITEM:CONTENT {
                    title: {title},
                    body: {body},
                    images: {images},
                    user: {userId},
                    date: {date},
                    upvotes: 0,
                    downvotes: 0,
                    comments: 0,
                    favorites: 0
                })-[:ASSOCIATED_WITH]->(i)
                RETURN  id(d) as id,
                        d.title as title,
                        d.body as body,
                        d.images as images,
                        d.date as date,
                        d.upvotes as upvotes,
                        d.downvotes as downvotes,
                        d.comments as comments
            ', array(
                'title' => $title,
                'body' => $body,
                'images' => $imagesRes,
                'userId' => $userId,
                'interestId' => $interestId,
                'date' => time()
            ));
        }

        if ($itemId) {
            if (!$itemId[0]['images']) {
                $itemId[0]['images'] = null;
            }
            return $itemId[0];
        }

        return false;
    }

    public function edit($itemId, $title, $body, $images, $userId, $interestId)
    {
        $imagesRes = $this->processImages($images);

        if ($interestId === 0) {
            $itemId = $this->sendCypherQuery('
                MATCH   (u:USER), (i:ITEM)
                WHERE   id(u) = {userId}
                AND     id(i) = {itemId}
                WITH    u, i
                SET     i.title = {title}
                SET     i.body = {body}
                SET     i.images: {images},
                SET     i.user: {userId},
                SET     i.date: {date},
                RETURN  id(i) as id,
                        i.title as title,
                        i.body as body,
                        i.images as images,
                        i.date as date,
                        i.upvotes as upvotes,
                        i.downvotes as downvotes,
                        i.comments as comments
            ', array(
                'title' => $title,
                'body' => $body,
                'images' => $imagesRes,
                'userId' => $userId,
                'itemId' => $itemId,
                'date' => time()
            ));
        } else {
            $itemId = $this->sendCypherQuery('
                MATCH   (u:USER), (i:ITEM)
                WHERE   id(u) = {userId}
                AND     id(i) = {itemId}
                WITH    u, i
                SET     i.title = {title}
                SET     i.body = {body}
                SET     i.images = {images}
                SET     i.user = {userId}
                SET     i.date = {date}
                SET     i.interestId = {interestId}
                RETURN  id(i) as id,
                        i.title as title,
                        i.body as body,
                        i.images as images,
                        i.date as date,
                        i.upvotes as upvotes,
                        i.downvotes as downvotes,
                        i.comments as comments
            ', array(
                'title' => $title,
                'body' => $body,
                'images' => $imagesRes,
                'userId' => $userId,
                'itemId' => $itemId,
                'interestId' => $interestId,
                'date' => time()
            ));
        }

        if ($itemId) {
            if (!$itemId[0]['images']) {
                $itemId[0]['images'] = null;
            }
            return $itemId[0];
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
        $fs = new Filesystem();

        if (is_array($images)) {
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

                $fs->dumpFile($saveLocation, $image);
                $files[] = $webLocation;
            }
        }

        return $files;
    }

    private function saveData($saveLocation, $webLocation, $content)
    {
        $fs = new Filesystem();
        $fs->dumpFile($saveLocation, $content);

        return $webLocation;
//
//        $temp = tempnam(sys_get_temp_dir(), 'temp');
//
//        if (!($f = fopen($temp, 'wb'))) {
//            $temp = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('temp');
//            if (!($f = fopen($temp, 'wb'))) {
//                trigger_error(sprintf('Error writing temp file `%s`', $temp), E_USER_WARNING);
//                return false;
//            }
//        }
//
//        fwrite($f, $content);
//        fclose($f);
//
//        if (!rename($temp, $saveLocation)) {
//            unlink($saveLocation);
//            rename($temp, $saveLocation);
//        }
//
//        chmod($saveLocation, 0777);
//
//        return $webLocation;
    }

}
