<?php

namespace SV\UserMentionsImprovements\Repository;

use SV\UserMentionsImprovements\Globals;
use XF\Entity\UserGroup;
use XF\Mvc\Entity\Repository;
use function array_key_exists;

class UserMentions extends Repository
{
    public function findUsersByGroup(UserGroup $userGroup): \XF\Finder\User
    {
        /** @var \XF\Finder\User $finder */
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
        if (\count($mentionedUserGroups) === 0)
        {
            return $users;
        }

        $additionalUsers = \XF::db()->fetchAll('
            SELECT DISTINCT `user`.user_id, `user`.username, `relation`.user_group_id
            FROM xf_user AS `user`
            JOIN xf_user_group_relation AS `relation` ON `relation`.user_id = `user`.user_id
            WHERE `relation`.user_group_id IN (' . \XF::db()->quote(\array_keys($mentionedUserGroups)) . ')
        ');

        if (\count($additionalUsers) === 0)
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

            $mentionedUgUsers[$userId][] = ['title' => $group['title'], 'id' => $group['user_group_id']];
        }

        Globals::$userGroupMentionedIds = $mentionedUgUsers;
        \XF::runOnce('userMentionImprovementCleanup', function () {
            Globals::$userGroupMentionedIds = [];
        });

        return $users;
    }
}
