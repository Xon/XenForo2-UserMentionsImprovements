<?php

namespace SV\UserMentionsImprovements\XF\Service\StructuredText;

class Preparer extends XFCP_Preparer
{
	protected $mentionedUserGroups = [];

	protected function filterFinalUserMentions($null, $string)
	{
		$string = parent::filterFinalUserMentions($null, $string);

		/** @var \SV\UserMentionsImprovements\Str\UserGroupMentionFormatter $mentions */
		$mentions = $this->app->stringFormatter()->getUserGroupMentionFormatter();

		$string = $mentions->getMentionsStructuredText($string);
		$this->mentionedUserGroups = $mentions->getMentionedUserGroups();

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
			$users[$userId] = [
				'user_id' => $additionalUser['user_id'],
				'username' => $additionalUser['username'],
				'lower' => strtolower($additionalUser['username'])
			];
		}

		return $users;
	}
}