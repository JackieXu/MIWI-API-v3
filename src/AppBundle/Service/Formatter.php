<?php


namespace AppBundle\Service;

/**
 * Class Formatter
 *
 * Handles node formatting into usable arrays.
 *
 * @package AppBundle\Service
 */
class Formatter extends BaseManager
{
    /**
     * Format article
     *
     * @param array $content
     * @param integer $userId
     * @return array
     */
    public function formatArticle($content, $userId)
    {
        $formattedAuthor = $this->formatUser($content['user']);
        $hasCommented = false;
        $isAdmin = $content['user'] === $userId;

        $isFavorited = count($this->sendCypherQuery('
            MATCH   (u:USER)-[r:HAS_FAVORITED]->(c:CONTENT)
            WHERE   id(u) = {userId}
            AND     id(c) = {contentId}
            RETURN  r
        ', array(
            'userId' => $userId,
            'contentId' => $content['id']
        ))) > 0;

        $sharedBy = $this->sendCypherQuery('
            MATCH   (f:USER)-[fu:FRIENDS_WITH]-(u:USER),
                    (f)-[fc:HAS_SHARED]->(c:CONTENT)
            WHERE   id(u) = {userId}
            AND     id(c) = {contentId}
            RETURN  id(f) as id
        ', array(
            'userId' => $userId,
            'contentId' => $content['id']
        ));

        if ($sharedBy) {
            $sharedBy = $this->formatUser($sharedBy[0]);
        } else {
            $sharedBy = null;
        }

        $images = array();
        if (is_array($content['images'])) {
            $images = $content['images'];
        } elseif (is_string($content['images'])) {
            $images = explode(',', $content['images']);

            if (is_string($images) && strlen($images) > 5) {
                $images = array($images);
            }
        }


        return array(
            'id' => $content['id'],
            'date' => $content['date'],
            'type' => 'article',
            'body' => $content['body'],
            'interestId' => $content['interestId'],
            'images' => $images,
            "link" => $content['link'],
            'title' => $content['title'],
            'commentCount' => $content['comments'],
            'hasCommented' => $hasCommented,
            'hasFavorited' => $isFavorited,
            'postedBy' => $formattedAuthor,
            'isAdmin' => $isAdmin,
            'sharedBy' => $sharedBy
        );

    }

    public function formatPost($content, $userId, $maxComments)
    {
        $formattedComments = $this->getComments((int) $content->getId(), $userId, $maxComments);

        $postedBy = array(
            'id' => -1,
            'name' => 'MIWI',
            'firstName' => 'MIWI',
            'lastName' => 'MIWI',
            'image' => 'http://www.miwi.com/img/default/user_image.png'
        );

        if ($content->hasProperty('user')) {
            $user = $this->sendCypherQuery('
                MATCH   (u:USER)
                WHERE   id(u) = {id}
                RETURN  u
            ', array(
                'id' => (int)$content->getProperty('user')
            ))->getResult()->getSingleNode('USER');

            $postedBy = $this->formatUser($user);
        }

        if (is_null($userId)) {
            return array(
                'id' => $content->getId(),
                'date' => ($content->hasProperty('date') && (!is_null($content->getProperty('date')))) ? $content->getProperty('date') : time(),
                'type' => 'post',
                'body' => $content->getProperty('body'),
                'interestId' => $content->hasProperty('interestId') ? $content->getProperty('interestId') : null,
                'images' => ($content->hasProperty('images') && (!is_null($content->getProperty('images')))) ? (is_array($content->getProperty('images')) ? $content->getProperty('images') : array($content->getProperty('images'))) : array(),
                'likes' => is_null($content->getProperty('likes')) ? 0 : $content->getProperty('likes'),
                'shares' => ($content->hasProperty('shares') && (!is_null($content->getProperty('shares')))) ? $content->getProperty('shares') : 0,
                'comments' => $formattedComments,
                'commentCount' => ($content->hasProperty('comments') && (!is_null($content->getProperty('comments')))) ? $content->getProperty('comments') : 0,
                'postedBy' => $postedBy
            );
        } else {
            $isSaved = count($this->sendCypherQuery('
                MATCH   (u:USER)-[r:HAS_SAVED]->(c:CONTENT)
                WHERE   id(u) = {userId}
                AND     id(c) = {contentId}
                RETURN  r
            ', array(
                    'userId' => $userId,
                    'contentId' => (int)$content->getId()
                ))->getRows()) !== 0;

            $isLiked = count($this->sendCypherQuery('
                MATCH   (u:USER)-[r:HAS_LIKED]->(c)
                WHERE   id(u) = {userId}
                AND     id(c) = {contentId}
                RETURN  r
            ', array(
                    'userId' => $userId,
                    'contentId' => $content->getId(),
                ))->getRows()) > 0;

            $hasCommented = false;
            $hasShared = false;
            $isAdmin = false;
            $audience = 'everyone';

            if ($content->hasProperty('user')) {
                if (((int)$content->getProperty('user')) === $userId) {
                    $isAdmin = true;
                }
            }

            if ($isAdmin) {
                $code = $this->sendCypherQuery('
                    MATCH   (u:USER)-[r:HAS_POSTED]->(p:POST)
                    WHERE   id(u) = {userId}
                    AND     id(p) = {contentId}
                    RETURN  r.audience as audience
                ', array(
                    'userId' => $userId,
                    'contentId' => (int) $content->getId()
                ))->getResult()->get('audience');

                switch ($code) {
                    case 1:
                        $audience = 'friends';
                        break;
                    case 2:
                        $audience = 'me';
                        break;
                }
            }

            $isFavorited = count($this->sendCypherQuery('
                MATCH   (u:USER)-[r:HAS_FAVORITED]->(c:CONTENT)
                WHERE   id(u) = {userId}
                AND     id(c) = {contentId}
                RETURN  r
            ', array(
                    'userId' => $userId,
                    'contentId' => (int) $content->getId()
                ))->getRows()) !== 0;

            $sharedBy = $this->sendCypherQuery('
                MATCH   (f:USER)-[fu:FRIENDS_WITH]-(u:USER),
                        (f)-[fc:HAS_SHARED]->(c:CONTENT)
                WHERE   id(u) = {userId}
                AND     id(c) = {contentId}
                RETURN  f
            ', array(
                'userId' => $userId,
                'contentId' => (int) $content->getId()
            ))->getResult()->getSingleNode('USER');

            $canShare = $isAdmin;

            if (!$canShare) {

                $canShare = count($this->sendCypherQuery('
                    MATCH   (a:ARTICLE)
                    WHERE   id(a) = {contentId}
                    RETURN  a
                ', array(
                        'contentId' => (int)$content->getId()
                    ))->getRows()) > 0;

            }

            if (!$canShare) {

                $canShare = count($this->sendCypherQuery('
                    MATCH   (f:USER)-[fc:HAS_SHARED]->(c:CONTENT)
                    WHERE   id(f) <> {userId}
                    AND     id(c) = {contentId}
                    AND     fc.audience = 0
                    RETURN  c
                ', array(
                        'userId' => $userId,
                        'contentId' => (int)$content->getId(),
                    ))->getRows()) > 0;

                if (!$canShare) {
                    $canShare = count($this->sendCypherQuery('
                        MATCH   (u:USER)-[uf:FRIENDS_WITH]-(f:USER),
                                (f)-[fc:HAS_SHARED]->(c:CONTENT)
                        WHERE   id(f) <> {userId}
                        AND     id(c) = {contentId}
                        AND     f.friendsCanShare = {canShare}
                        AND     fc.audience < 2
                        RETURN  c
                    ', array(
                            'userId' => $userId,
                            'contentId' => (int)$content->getId(),
                            'canShare' => true
                        ))->getRows()) > 0;
                }
            }

            if ($sharedBy) {
                $sharedBy = $this->formatUser($sharedBy);
            } else {
                $sharedBy = null;
            }

            $images = array();

            if ($content->hasProperty('images')) {
                if (is_array($content->getProperty('images'))) {
                    $images = $content->getProperty('images');
                } elseif (is_string($content->getProperty('images'))) {
                    $images = explode(',', $content->getProperty('images'));

                    if (is_string($images) && strlen($images) > 5) {
                        $images = array($images);
                    }
                }
            }

            return array(
                'id' => $content->getId(),
                'date' => ($content->hasProperty('date') && (!is_null($content->getProperty('date')))) ? $content->getProperty('date') : time(),
                'type' => 'post',
                'audience' => $audience,
                'body' => $content->getProperty('body'),
                'interestId' => $content->hasProperty('interestId') ? $content->getProperty('interestId') : null,
                'images' => $images,
                'likes' => is_null($content->getProperty('likes')) ? 0 : $content->getProperty('likes'),
                'shares' => ($content->hasProperty('shares') && (!is_null($content->getProperty('shares')))) ? $content->getProperty('shares') : 0,
                'comments' => $formattedComments,
                'commentCount' => ($content->hasProperty('comments') && (!is_null($content->getProperty('comments')))) ? $content->getProperty('comments') : 0,
                'hasCommented' => $hasCommented,
                'hasShared' => $hasShared,
                'hasSaved' => $isSaved,
                'hasLiked' => $isLiked,
                'hasFavorited' => $isFavorited,
                'postedBy' => $postedBy,
                'isAdmin' => $isAdmin,
                'sharedBy' => $sharedBy,
                'canShare' => $canShare
            );
        }
    }

    /**
     * Returns formatted content node
     *
     * This method is for handling user generated posts, there
     * is a `formatArticle` method for crawled article formatting.
     *
     * @param Node $content
     * @param integer $userId
     * @param int $maxComments
     * @return array
     */
    public function formatContent(Node $content, $userId, $maxComments = 1000)
    {
        if (in_array('ARTICLE', $content->getLabels())) {
            return $this->formatArticle($content, $userId, $maxComments);
        }

        return $this->formatPost($content, $userId, $maxComments);
    }

    /**
     * Returns a formatted comment node
     *
     * @param Node $comment
     * @param integer $userId
     * @return array
     */
    public function formatComment(Node $comment, $userId)
    {
        $user = $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
            RETURN  u
        ', array(
            'userId' => (int) $comment->getProperty('user')
        ))->getResult()->getSingleNode('USER');

        $formattedUser = $this->formatUser($user);

        if (is_null($userId)) {
            return array(
                'id' => $comment->getId(),
                'parentId' => $comment->getProperty('parentId'),
                'date' => $comment->getProperty('date'),
                'text' => $comment->getProperty('text'),
                'isAdmin' => false,
                'likes' => is_null($comment->getProperty('likes')) ? 0 : $comment->getProperty('likes'),
                'hasLiked' => false,
                'postedBy' => $formattedUser,
                'type' => 'comment'
            );
        } else {
            $hasLiked = count($this->sendCypherQuery('
                MATCH   (u:USER)-[r:HAS_LIKED]->(c:COMMENT)
                WHERE   id(u) = {userId}
                AND     id(c) = {contentId}
                RETURN  r
            ', array(
                    'userId' => $userId,
                    'contentId' => (int) $comment->getId()
                ))->getRows()) !== 0;

            return array(
                'id' => $comment->getId(),
                'parentId' => $comment->getProperty('parentId'),
                'date' => $comment->getProperty('date'),
                'text' => $comment->getProperty('text'),
                'isAdmin' => $userId == $formattedUser['id'],
                'likes' => is_null($comment->getProperty('likes')) ? 0 : $comment->getProperty('likes'),
                'hasLiked' => $hasLiked,
                'postedBy' => $formattedUser,
                'type' => 'comment'
            );
        }
    }

    /**
     * Returns a formatted group node
     *
     * @param Node $group
     * @param integer $userId
     * @return array
     */
    public function formatGroup($group, $userId)
    {
        $isMember = count($this->sendCypherQuery('
            MATCH   (u:USER)-[r:MEMBER_OF]->(g:GROUP)
            WHERE   id(u) = {userId}
            AND     id(g) = {groupId}
            RETURN  g
        ', array(
                'userId' => $userId,
                'groupId' => (int) $group->getId()
            ))->getRows()) > 0;

        $isAdmin = count($this->sendCypherQuery('
            MATCH   (u:USER)-[r:ADMIN_OF]->(g:GROUP)
            WHERE   id(u) = {userId}
            AND     id(g) = {groupId}
            RETURN  g
        ', array(
                'userId' => $userId,
                'groupId' => (int) $group->getId()
            ))->getRows()) > 0;

        if ($group->hasProperty('user')) {
            $createdBy = $this->sendCypherQuery('
                MATCH   (u:USER)
                WHERE   id(u) = {userId}
                RETURN  u
            ', array(
                'userId' => $group->getProperty('user')
            ));
        } else {
            $createdBy = null;
        }

        $invitedBy = $this->sendCypherQuery('
            MATCH   (u:USER)-[r:MEMBER_OF]->(g:GROUP), (v:USER)
            WHERE   id(u) = {userId}
            AND     id(g) = {groupId}
            AND     id(v) = r.invitedBy
            RETURN  v
        ', array(
            'userId' => $userId,
            'groupId' => (int) $group->getId()
        ))->getRows();

        if (count($createdBy) > 0) {
            $node = $createdBy[0];
            $createdBy = $this->formatUser($node);
        } else {
            $createdBy = null;
        }

        if (count($invitedBy) > 0) {
            $node = array_shift($invitedBy);
            $invitedBy = $this->formatUser($node);
        } else {
            $invitedBy = null;
        }

        return array(
            'id' => $group->getId(),
            'image' => $group->hasProperty('image') ? $group->getProperty('image') : 'http://www.miwi.com/img/default/group_image.png',
            'banner' => $group->hasProperty('background') ? $group->getProperty('background') : 'http://www.miwi.com/img/default/group_image.png',
            'title' => $group->getProperty('title'),
            'body' => $group->hasProperty('body') ? $group->getProperty('body') : '',
            'members' => is_null($group->getProperty('members')) ? 0 : $group->getProperty('members'),
            'visibility' => $group->hasProperty('visibility') ? $group->getProperty('visibility') : 'public',
            'website' => $group->getProperty('website'),
            'interestId' => $group->getProperty('interestId'),
            'createdBy' => $createdBy,
            'invitedBy' => $invitedBy,
            'isMember' => $isMember,
            'isAdmin' => $isAdmin,
            'status' => $this->getGroupStatus($userId, (int) $group->getId()),
            'type' => 'group'
        );
    }

    /**
     * Returns a formatted event node
     *
     * @param Node $event
     * @param integer $userId
     * @return array
     */
    public function formatEvent(Node $event, $userId)
    {
        $isAttending = count($this->sendCypherQuery('
            MATCH   (u:USER)-[r:IS_ATTENDING]->(e:EVENT)
            WHERE   id(u) = {userId}
            AND     id(e) = {eventId}
            RETURN  e
        ', array(
                'userId' => $userId,
                'eventId' => (int) $event->getId()
            ))->getRows()) > 0;

        $isAdmin = count($this->sendCypherQuery('
            MATCH   (u:USER)-[r:ADMIN_OF]->(e:EVENT)
            WHERE   id(u) = {userId}
            AND     id(e) = {eventId}
            RETURN  e
        ', array(
                'userId' => $userId,
                'eventId' => (int) $event->getId()
            ))->getRows()) > 0;

        if ($event->hasProperty('user')) {
            $createdBy = $this->sendCypherQuery('
                MATCH   (u:USER)
                WHERE   id(u) = {userId}
                RETURN  u
            ', array(
                'userId' => $event->getProperty('user')
            ));
        } else {
            $createdBy = null;
        }

        $invitedBy = $this->sendCypherQuery('
            MATCH   (u:USER)-[r:IS_ATTENDING]->(g:EVENT), (v:USER)
            WHERE   id(u) = {userId}
            AND     id(g) = {eventId}
            AND     id(v) = r.invitedBy
            RETURN  v
        ', array(
            'userId' => $userId,
            'eventId' => (int) $event->getId()
        ));

        if (count($createdBy) > 0) {
            $node = $createdBy[0];
            $createdBy = $this->formatUser($node);
        } else {
            $createdBy = null;
        }

        if (count($invitedBy) > 0) {
            $node = $invitedBy[0];
            $invitedBy = $this->formatUser($node);
        } else {
            $invitedBy = null;
        }

        return array(
            'id' => $event->getId(),
            'type' => 'event',
            'title' => $event->getProperty('title'),
            'body' => $event->getProperty('body'),
            'banner' => $event->hasProperty('background') ? $event->getProperty('background') : '',
            'startDate' => $event->hasProperty('startDate') ? $event->getProperty('startDate') : null,
            'endDate' => $event->hasProperty('endDate') ? $event->getProperty('endDate') : null,
            'location' => $event->hasProperty('location') ? $event->getProperty('location') : null,
            'website' => $event->getProperty('website'),
            'members' => is_null($event->getProperty('members')) ? 0 : $event->getProperty('members'),
            'isAttending' => $isAttending,
            'visibility' => $event->getProperty('visibility'),
            'isAdmin' => $isAdmin,
            'createdBy' => $createdBy,
            'invitedBy' => $invitedBy,
            'interestId' => $event->getProperty('interestId'),
            'groupId' => $event->hasProperty('groupId') ? $event->getProperty('groupId') : null,
            'status' => $this->getEventStatus($userId, (int) $event->getId())
        );
    }

    /**
     * Returns a formatted user node
     *
     * @param Node|null $user
     * @return array
     */
    public function formatUser($user)
    {
        if (!($user instanceof Node)) {
            return array(
                'id' => -1,
                'name' => 'MIWI',
                'firstName' => 'MIWI',
                'lastName' => '',
                'image' => 'http://www.miwi.com/img/default/user_image.png'
            );
        }

        return array(
            'id' => $user->getId(),
            'name' => $user->getProperty('username'),
            'firstName' => $user->hasProperty('firstName') ? $user->getProperty('firstName') : '',
            'lastName' => $user->hasProperty('lastName') ? $user->getProperty('lastName') : '',
            'image' => $user->getProperty('image')
        );
    }

    /**
     * Returns a formatted interest node
     *
     * @param Node $interest
     * @return array
     */
    public function formatInterest(Node $interest)
    {
        return array(
            'id' => $interest->getId(),
            'name' => ucwords($interest->getProperty('name'))
        );
    }

    /**
     * Returns a formatted interest node for profiles
     *
     * @param Node $interest
     * @param integer $profileId
     * @param integer $userId
     * @return array
     */
    public function formatProfileInterest(Node $interest, $profileId, $userId)
    {
        $formattedInterest = $this->formatInterest($interest);
        $formattedInterest['isFollowing'] = false;

        if ($profileId === $userId) {

            $formattedInterest['isFollowing'] = true;

        } else {

            $followingInterests = $this->sendCypherQuery('
                MATCH           (u:USER)-[r:IS_FOLLOWING]->(f:USER)
                WHERE           id(u) = {userId}
                AND             id(f) = {friendId}
                RETURN          r.interest as interests
            ', array(
                'userId' => $userId,
                'friendId' => $profileId
            ))->getRows();

            if (count($followingInterests)) {

                $interests = $followingInterests['interests'];

                if (in_array($formattedInterest['id'], $interests)) {
                    $formattedInterest['isFollowing'] = true;
                }

            }

        }

        return $formattedInterest;
    }

    /**
     * @param Node $conversation
     * @param array $messages
     * @param integer $userId
     * @return array
     */
    public function formatDetailedConversation(Node $conversation, array $messages, $userId)
    {
        $formattedConversation = $this->formatConversation($conversation, $userId);
        $formattedMessages = array();

        foreach ($messages as $message) {
            $formattedMessages[] = $this->formatMessage($message);
        }

        unset($formattedConversation['lastMessage']);
        $formattedConversation['messages'] = $formattedMessages;

        return $formattedConversation;
    }

    public function formatConversation(Node $conversation, $userId)
    {
        $latestMessage = $this->sendCypherQuery('
            MATCH       (m:MESSAGE)-[r:MESSAGE_IN]->(c:CONVERSATION)
            WHERE       id(c) = {conversationId}
            RETURN      m
            ORDER BY    m.date DESC
            LIMIT       1
        ', array(
            'conversationId' => (int) $conversation->getId()
        ));

        $friends = $this->sendCypherQuery('
            MATCH   (u:USER)-[r:PART_OF]->(c:CONVERSATION)
            WHERE   id(c) = {conversationId}
            AND     id(u) <> {userId}
            RETURN  u
        ', array(
            'conversationId' => (int) $conversation->getId(),
            'userId' => $userId
        ));

        $formattedFriends = array();

        foreach ($friends as $friend) {
            $formattedFriends[] = $this->formatUser($friend);
        }

        return array(
            'id' => $conversation->getId(),
            'date' => $conversation->getProperty('date'),
            'friends' => $formattedFriends,
            'lastMessage' => $this->formatMessage(array_shift($latestMessage))
        );
    }

    /**
     * Returns a formatted message node
     *
     * @param Node $message
     * @return array
     */
    public function formatMessage(Node $message)
    {
        $author = $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
            RETURN  u
        ', array(
            'userId' => $message->getProperty('author')
        ));

        return array(
            'id' => $message->getId(),
            'message' => $message->getProperty('message'),
            'date' => $message->getProperty('date'),
            'author' => $this->formatUser(array_shift($author))
        );
    }

    /**
     * Returns a formatted friend node
     *
     * @param Node $friend
     * @param $userId
     * @return array
     */
    public function formatFriend(Node $friend, $userId)
    {
        $similarInterests = $this->sendCypherQuery('
            MATCH   (f:USER)-[r:LIKES]->(i:INTEREST)<-[q:LIKES]-(u:USER)
            WHERE   id(f) = {friendId}
            AND     id(u) = {userId}
            RETURN  count(i) as count
        ', array(
            'userId' => $userId,
            'friendId' => (int) $friend->getId()
        ))->getResult()->get('count');

        $friendArray = $this->formatUser($friend);
        $friendArray['type'] = 'buddy';
        $friendArray['similarInterests'] = $similarInterests;
        return $friendArray;
    }

    /**
     * Returns a formatted settings node
     *
     * @param Node $user
     * @param array $errors
     * @return array
     */
    public function formatSettings(Node $user, array $errors)
    {
        $interests = $this->sendCypherQuery('
            MATCH   (u)-[r:LIKES]-(i:INTEREST)
            WHERE   id(u) = {userId}
            AND     r.type <> {deleted}
            RETURN  i
        ', array(
            'userId' => (int) $user->getId(),
            'deleted' => 'deleted'
        ));

        $formattedInterests = array(
            'active' => array(),
            'archived' => array()
        );

        foreach ($interests as $interest) {

            $formattedInterest = $this->formatInterest($interest);
            $formattedInterest['archived'] = $this->getInterestStatus((int) $user->getId(), (int) $formattedInterest['id']);
            $formattedInterest['visibility'] = $this->getInterestVisibility((int) $user->getId(), (int) $formattedInterest['id']);
            $formattedInterest['deleted'] = false;

            if ($formattedInterest['archived']) {
                $formattedInterests['archived'][] = $formattedInterest;
            } else {
                $formattedInterests['active'][] = $formattedInterest;
            }
        }

        return array(
            'account' => array(
                'firstName' => $user->hasProperty('firstName') ? $user->getProperty('firstName') : '',
                'lastName' => $user->hasProperty('lastName') ? $user->getProperty('lastName') : '',
                'name' => $user->getProperty('username'),
                'bio' => $user->hasProperty('bio') ? $user->getProperty('bio') : '',
                'location' => $user->hasProperty('location') ? $user->getProperty('location') : '',
                'website' => $user->hasProperty('website') ? $user->getProperty('website') : '',
                'image' => $user->hasProperty('image') ? $user->getProperty('image') : '',
                'banner' => $user->hasProperty('banner') ? $user->getProperty('banner') : '',
                'birthday' => $user->hasProperty('birthday') ? $user->getProperty('birthday') : null
            ),
            'security' => array(
                'email' => $user->getProperty('email'),
                'password' => '',
                'current' => ''
            ),
            'privacy' => array(
                'profile' => $user->hasProperty('profile') ? $user->getProperty('profile') : 'everyone',
                'interests' =>  $user->hasProperty('interests') ? $user->getProperty('interests') : 'everyone',
                'events' => $user->hasProperty('events') ? $user->getProperty('events') : 'everyone',
                'groups' => $user->hasProperty('groups') ? $user->getProperty('groups') : 'everyone',
                'favorites' => $user->hasProperty('favorites') ? $user->getProperty('favorites') : 'everyone',
                'friendsCanShare' => $user->hasProperty('friendsCanShare') ? (boolean) $user->getProperty('friendsCanShare') : true,
            ),
            'notifications' => array(
                'email' => array(
                    'likes' => $user->hasProperty('emailLikes') ? (boolean) $user->getProperty('emailLikes') : true,
                    'mentions' => $user->hasProperty('emailMentions') ? (boolean) $user->getProperty('emailMentions') : true,
                    'activityMailFrequency' => $user->hasProperty('activityMailFrequency') ? $user->getProperty('activityMailFrequency') : 'weekly',
                    'newsletter' => $user->hasProperty('newsletter') ? (boolean) $user->getProperty('newsletter') : true
                ),
                'app' => array(
                    'likes' => $user->hasProperty('appLikes') ? (boolean) $user->getProperty('appLikes') : true,
                    'events' => $user->hasProperty('appEvents') ? (boolean) $user->getProperty('appEvents') : true,
                    'mentions' => $user->hasProperty('appMentions') ? (boolean) $user->getProperty('appMentions') : true,
                    'messages' => $user->hasProperty('appMessages') ? (boolean) $user->getProperty('appMessages') : true,
                    'comments' => $user->hasProperty('appComments') ? (boolean) $user->getProperty('appComments') : true,
                    'activity' => $user->hasProperty('appActivity') ? (boolean) $user->getProperty('appActivity') : true,
                )
            ),
            'social' => array(
                'facebook' => false
            ),
            'interests' => $formattedInterests,
            'errors' => $errors
        );
    }

    /**
     * Returns a formatted notification node
     *
     * @param Node $notification
     * @return array
     */
    public function formatNotification(Node $notification)
    {
        $formattedPeople = array();

        foreach ($notification->getProperty('people') as $n => $personId) {
            $person = $this->sendCypherQuery('
                MATCH   (u:USER)
                WHERE   id(u) = {userId}
                RETURN  u
            ', array(
                'userId' => $personId
            ))->getResult()->getSingleNode('USER');

            if ($person) {
                $formattedPeople[] = $this->formatUser($person);
            }
        }

        $isRead = false;

        if ($notification->hasProperty('isRead') && $notification->getProperty('isRead')) {
            $isRead = true;
        }

        return array(
            'id' => (int) $notification->getId(),
            'date' => $notification->getProperty('date'),
            'objectId' => $notification->getProperty('objectId'),
            'objectType' => $notification->getProperty('objectType'),
            'type' => $notification->getProperty('type'),
            'people' => $formattedPeople,
            'isRead' => $isRead
        );
    }

    /**
     * Gets user status to group
     *
     * @param integer $userId
     * @param integer $groupId
     * @return integer
     */
    protected function getGroupStatus($userId, $groupId)
    {
        $isAdmin = $this->sendCypherQuery('
            MATCH   (u:USER)-[r:ADMIN_OF]->(g:GROUP)
            WHERE   id(u) = {userId}
            AND     id(g) = {groupId}
            RETURN  g
        ', array(
            'userId' => $userId,
            'groupId' => $groupId
        ))->getRows();

        if (count($isAdmin) > 0) {
            return 3;
        }

        $isMember = $this->sendCypherQuery('
            MATCH   (u:USER)-[r:MEMBER_OF]->(g:GROUP)
            WHERE   id(u) = {userId}
            AND     id(g) = {groupId}
            RETURN  g
        ', array(
            'userId' => $userId,
            'groupId' => $groupId
        ))->getRows();

        if (count($isMember) > 0) {
            return 2;
        }

        $isInvited = $this->sendCypherQuery('
            MATCH   (u:USER)-[r:INVITED_TO]->(g:GROUP)
            WHERE   id(u) = {userId}
            AND     id(g) = {groupId}
            RETURN  g
        ', array(
            'userId' => $userId,
            'groupId' => $groupId
        ))->getRows();

        if (count($isInvited) > 0) {
            return 1;
        }

        return 0;
    }

    /**
     * Gets user status to event
     *
     * @param integer $userId
     * @param integer $eventId
     * @return integer
     */
    protected function getEventStatus($userId, $eventId)
    {
        $isInvited = $this->sendCypherQuery('
            MATCH   (u:USER)-[r:INVITED_TO]->(e:EVENT)
            WHERE   id(u) = {userId}
            AND     id(e) = {eventId}
            RETURN  e
        ', array(
            'userId' => $userId,
            'eventId' => $eventId
        ))->getRows();

        if (count($isInvited) > 0) {
            return 1;
        }

        $isMember = $this->sendCypherQuery('
            MATCH   (u:USER)-[r:IS_ATTENDING]->(e:EVENT)
            WHERE   id(u) = {userId}
            AND     id(e) = {eventId}
            RETURN  e
        ', array(
            'userId' => $userId,
            'eventId' => $eventId
        ))->getRows();

        if (count($isMember)) {
            return 2;
        }

        $isAdmin = $this->neoClient->getClient('
            MATCH   (u:USER)-[r:ADMIN_OF]->(e:EVENT)
            WHERE   id(u) = {userId}
            AND     id(e) = {eventId}
            RETURN  e
        ', array(
            'userId' => $userId,
            'eventId' => $eventId
        ))->getRows();

        if (count($isAdmin)) {
            return 3;
        }

        return 0;
    }

    protected function getInterestStatus($userId, $interestId)
    {
        $isNew = $this->sendCypherQuery('
            MATCH   (u:USER)-[r:LIKES]->(i:INTEREST)
            WHERE   id(u) = {userId}
            AND     id(i) = {interestId}
            RETURN  r
        ', array(
            'userId' => $userId,
            'interestId' => $interestId
        ));

        if (count($isNew) === 0) {
            return true;
        }

        $relationship = array_values($isNew)[0];

        return $relationship->getProperty('type') === 'active' ? false : true;
    }

    protected function getInterestVisibility($userId, $interestId)
    {
        $isNew = $this->sendCypherQuery('
            MATCH   (u:USER)-[r:LIKES]->(i:INTEREST)
            WHERE   id(u) = {userId}
            AND     id(i) = {interestId}
            RETURN  r
        ', array(
            'userId' => $userId,
            'interestId' => $interestId
        ));

        if (count($isNew) === 0) {
            return true;
        }

        $relationship = array_values($isNew)[0];

        return $relationship->getProperty('visibility') === 'private' ? 'private' : 'public';
    }

    public function formatUserDevices(Node $user)
    {
        if (!($user instanceof Node)) {
            return array();
        }

        $devices = array();

        foreach ($user->getProperty('iosDevices') as $iosDeviceId) {
            $devices[] = array(
                'type' => 'ios',
                'id' => $iosDeviceId
            );
        }

        foreach ($user->getProperty('androidDevices') as $androidDeviceId) {
            $devices[] = array(
                'type' => 'android',
                'id' => $androidDeviceId
            );
        }

        return $devices;
    }
}
