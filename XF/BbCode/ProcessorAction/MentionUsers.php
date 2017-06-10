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

	public static function factory(\XF\App $app)
	{
		return new static($app->stringFormatter());
	}
}