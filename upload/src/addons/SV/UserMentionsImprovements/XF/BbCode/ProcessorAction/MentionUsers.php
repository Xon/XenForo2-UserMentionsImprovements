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

		$users = \XF::app()->repository('SV\UserMentionsImprovements:UserMentions')
			->mergeUserGroupMembersIntoUsersArray($users, $this->getMentionedUserGroups());

		return $users;
	}
}