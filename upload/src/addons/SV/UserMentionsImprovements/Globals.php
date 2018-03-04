<?php


namespace SV\UserMentionsImprovements;
use SV\UserMentionsImprovements\XF\Entity\User;

/**
 * Add-on globals.
 */
class Globals
{
    /**
     * @var array|null
     */
    public static $userGroupMentionedIds = null;

    /**
     * @param int $userId
     * @return User
     */
    public static function getUser($userId)
    {
        $users = self::getUsers([$userId]);
        return reset($users);
    }

    /**
     * @param int[] $userIds
     * @return User[]
     */
    public static function getUsers(array $userIds)
    {
        $em = \XF::em();
        /** @var User[] $users */
        $users = [];
        $userIdsToFetch = [];
        foreach($userIds as $userId)
        {
            $user = $em->findCached('XF:User', $userId);
            if ($user)
            {
                $users[$userId] = $user;
            }
            else
            {
                $userIdsToFetch[] = $userId;
            }
        }

        if ($userIdsToFetch)
        {
            $users = \array_merge($users, $em->findByIds('XF:User', $userIdsToFetch));
        }

        return $users;
    }

    /**
     * Private constructor, use statically.
     */
    private function __construct() {}
}
