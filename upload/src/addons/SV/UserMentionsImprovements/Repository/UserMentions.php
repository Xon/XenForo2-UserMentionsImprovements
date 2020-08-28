<?php

namespace SV\UserMentionsImprovements\Repository;

use SV\UserMentionsImprovements\Globals;
use XF\Entity\UserGroup;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Repository;

class UserMentions extends Repository
{
    /**
     * @param UserGroup $userGroup
     * @return \XF\Mvc\Entity\ArrayCollection|AbstractCollection
     */
    public function getMembersOfUserGroup(UserGroup $userGroup)
    {
        $users = $this->finder('XF:User')->where('UserGroupRelations.user_group_id', $userGroup->user_group_id);

        return $users->fetch();
    }

    /**
     * @param array            $users
     * @param UserGroup[]|null $mentionedUserGroups
     * @return array
     */
    public function mergeUserGroupMembersIntoUsersArray(array $users, $mentionedUserGroups)
    {
        if (!$mentionedUserGroups)
        {
            return $users;
        }

        $additionalUsers = \XF::db()->fetchAllKeyed(
            '
			SELECT DISTINCT user.user_id, user.username, relation.user_group_id
			FROM xf_user AS user
			LEFT JOIN xf_user_group_relation AS relation ON relation.user_id = user.user_id
            WHERE relation.user_group_id IN (' . \XF::db()->quote(array_keys($mentionedUserGroups)) . ')
		', 'user_id'
        );

        if (!$additionalUsers)
        {
            return $users;
        }

        $mentionedUgUsers = [];

        foreach ($additionalUsers AS $userId => $additionalUser)
        {
            if (isset($users[$userId]) || empty($mentionedUserGroups[$additionalUser['user_group_id']]))
            {
                continue;
            }

            $users[$userId] = [
                'user_id'  => $additionalUser['user_id'],
                'username' => $additionalUser['username'],
                'lower'    => strtolower($additionalUser['username']),
            ];

            $group = $mentionedUserGroups[$additionalUser['user_group_id']];

            $mentionedUgUsers[$userId] = ['title' => $group['title'], 'id' => $group['user_group_id']];
        }

        Globals::$userGroupMentionedIds = $mentionedUgUsers;
        \XF::runOnce('userMentionImprovementCleanup', function () {
            Globals::$userGroupMentionedIds = [];
        });

        return $users;
    }
}
