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

		$users = \XF::app()->repository('SV\UserMentionsImprovements:UserMentions')
			->mergeUserGroupMembersIntoUsersArray($users, $this->getMentionedUserGroups());

		return $users;
	}
}