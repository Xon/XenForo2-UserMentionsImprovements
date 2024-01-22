<?php

namespace SV\UserMentionsImprovements\Repository;

use SV\UserMentionsImprovements\Globals;
use XF\Entity\UserGroup;
use XF\Finder\User as UserFinder;
use XF\Mvc\Entity\Repository;
use function count, array_keys, array_key_exists;

class UserMentions extends Repository
{
    public static function get(): self
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return \XF::repository('SV\UserMentionsImprovements:UserMentions');
    }

    public function findUsersByGroup(UserGroup $userGroup): UserFinder
    {
        /** @var UserFinder $finder */
        $finder = $this->finder('XF:User')
                    ->where('UserGroupRelations.user_group_id', $userGroup->user_group_id)
                    ->order('user_id');

        return $finder;
    }

    /**
     * @param array       $users
     * @param UserGroup[] $mentionedUserGroups
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
