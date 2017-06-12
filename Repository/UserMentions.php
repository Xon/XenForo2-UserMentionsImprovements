<?php

namespace SV\UserMentionsImprovements\Repository;

use XF\Entity\UserGroup;
use XF\Mvc\Entity\Repository;

class UserMentions extends Repository
{
	/**
	 * @param \XF\Entity\UserGroup $userGroup
	 *
	 * @return \XF\Mvc\Entity\ArrayCollection
	 */
	public function getMembersOfUserGroup(UserGroup $userGroup)
	{
		$users = $this->finder('XF:User')->where('UserGroupRelations.user_group_id', $userGroup->user_group_id);

		return $users->fetch();
	}

	public function mergeUserGroupMembersIntoUsersArray(array $users, $mentionedUserGroups)
	{
		if (!$mentionedUserGroups)
		{
			return $users;
		}

		$additionalUsers = \XF::db()->fetchAllKeyed('
			SELECT DISTINCT user.user_id, user.username, relation.user_group_id
			FROM xf_user AS user
			LEFT JOIN xf_user_group_relation AS relation ON relation.user_id = user.user_id
            WHERE relation.user_group_id IN (' . \XF::db()->quote(array_keys($mentionedUserGroups)) . ')
		', 'user_id');

		if (!$additionalUsers)
		{
			return $users;
		}

		$mentionedUgUsers = [];

		foreach ($additionalUsers AS $userId => $additionalUser)
		{
			if (isset($users[$userId]))
			{
				continue;
			}

			$users[$userId] = [
				'user_id' => $additionalUser['user_id'],
				'username' => $additionalUser['username'],
				'lower' => strtolower($additionalUser['username'])
			];

			$mentionedUgUsers[$userId] = $mentionedUserGroups[$additionalUser['user_group_id']]['title'];
		}

		\XF::app()->sv_userGroupMentionedIds = $mentionedUgUsers;

		return $users;
	}
}