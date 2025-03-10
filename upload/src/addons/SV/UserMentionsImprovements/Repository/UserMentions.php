<?php

namespace SV\UserMentionsImprovements\Repository;

use SV\StandardLib\Helper;
use SV\UserMentionsImprovements\Globals;
use XF\Entity\UserGroup as UserGroupEntity;
use XF\Finder\User as UserFinder;
use XF\Mvc\Entity\Repository;
use function count, array_keys, array_key_exists;

class UserMentions extends Repository
{
    public static function get(): self
    {
        return Helper::repository(self::class);
    }

    public function findUsersByGroup(UserGroupEntity $userGroup): UserFinder
    {
        return Helper::finder(UserFinder::class)
                     ->where('UserGroupRelations.user_group_id', $userGroup->user_group_id)
                     ->order('user_id');
    }

    /**
     * @param array             $users
     * @param UserGroupEntity[] $mentionedUserGroups
     * @return array
     */
    public function mergeUserGroupMembersIntoUsersArray(array $users, array $mentionedUserGroups): array
    {
        if (count($mentionedUserGroups) === 0)
        {
            return $users;
        }

        $groupMentionIds = array_keys($mentionedUserGroups);
        $additionalUsers = \XF::db()->fetchAll('
            SELECT DISTINCT `user`.user_id, `user`.username, `relation`.user_group_id
            FROM xf_user AS `user`
            JOIN xf_user_group_relation AS `relation` ON `relation`.user_id = `user`.user_id
            WHERE `relation`.user_group_id IN (' . \XF::db()->quote($groupMentionIds) . ')
        ');

        if (count($additionalUsers) === 0)
        {
            return $users;
        }

        $mentionedUgUsers = [];
        foreach ($additionalUsers AS $additionalUser)
        {
            $userId = (int)$additionalUser['user_id'];

            if (!array_key_exists($userId, $users))
            {
                $users[$userId] = [
                    'user_id'  => $additionalUser['user_id'],
                    'username' => $additionalUser['username'],
                    'lower'    => \strtolower($additionalUser['username']),
                ];
            }

            $group = $mentionedUserGroups[$additionalUser['user_group_id']] ?? null;
            \assert($group !== null);

            $mentionedUgUsers[$userId][$group['user_group_id']] = ['title' => $group['title'], 'id' => $group['user_group_id']];
        }
        // preserve original ordering of user-group mentions as $additionalUsers is non-ordered
        foreach ($mentionedUgUsers as &$groups)
        {
            if (count($groups) <= 1)
            {
                $groups = array_values($groups);
                continue;
            }

            $orderedGroups = [];
            foreach ($groupMentionIds AS $groupId)
            {
                $id = (int)($groups[$groupId]['id'] ?? 0);
                if ($groupId === $id)
                {
                    $orderedGroups[$groupId] = $groups[$groupId];
                }
            }
            $groups = $orderedGroups;
        }

        Globals::$userGroupMentionedIds = $mentionedUgUsers;
        \XF::runOnce('userMentionImprovementCleanup', function () {
            Globals::$userGroupMentionedIds = [];
        });

        return $users;
    }
}
