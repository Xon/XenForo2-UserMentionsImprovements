<?php

namespace SV\UserMentionsImprovements\XF\BbCode\ProcessorAction;

class MentionUsers extends XFCP_MentionUsers
{
	protected $mentionedUserGroups = [];

	public function filterFinal($string)
	{
		$string = parent::filterFinal($string);

		$userGroupMentions = $this->formatter->getUserGroupMentionFormatter();

		$string = $userGroupMentions->getMentionsBbCode($string);
		$this->mentionedUserGroups = $userGroupMentions->getMentionedUserGroups();

		return $string;
	}

	public function getMentionedUserGroups()
	{
		return $this->mentionedUserGroups;
	}

	public function getMentionedUsers()
	{
		$users = parent::getMentionedUsers();

		if (!\XF::visitor()->canMentionUserGroup())
		{
			return $users;
		}

		$mentionedUsergroupIds = array_keys($this->getMentionedUserGroups());

		if (!$mentionedUsergroupIds)
		{
			return $users;
		}

		$additionalUsers = \XF::db()->fetchAllKeyed('
			SELECT DISTINCT user.user_id, user.username, relation.user_group_id
			FROM xf_user AS user
			LEFT JOIN xf_user_group_relation AS relation ON relation.user_id = user.user_id
            WHERE relation.user_group_id IN (' . \XF::db()->quote($mentionedUsergroupIds) . ')
		', 'user_id');

		if (!$additionalUsers)
		{
			return $users;
		}

		$mentionedUgUsers = [];

		$userGroups = $this->getMentionedUserGroups();

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

			$mentionedUgUsers[$userId] = $userGroups[$additionalUser['user_group_id']]['title'];
		}

		\XF::app()->sv_userGroupMentionedIds = $mentionedUgUsers;

		return $users;
	}
}