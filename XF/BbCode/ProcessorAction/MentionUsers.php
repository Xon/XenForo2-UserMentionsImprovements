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

		$mentionedUsergroupIds = array_keys($this->getMentionedUserGroups());

		if (!$mentionedUsergroupIds)
		{
			return $users;
		}

		$additionalUsers = \XF::db()->fetchAllKeyed('
			SELECT DISTINCT user.user_id, user.username
			FROM xf_user AS user
			LEFT JOIN xf_user_group_relation AS relation ON relation.user_id = user.user_id
            WHERE relation.user_group_id IN (' . \XF::db()->quote($mentionedUsergroupIds) . ')
		', 'user_id');

		if (!$additionalUsers)
		{
			return $users;
		}

		foreach ($additionalUsers AS $userId => $additionalUser)
		{
			$additionalUsers[$userId]['lower'] = strtolower($additionalUser['username']);
		}
		
		return $users + $additionalUsers;
	}
}