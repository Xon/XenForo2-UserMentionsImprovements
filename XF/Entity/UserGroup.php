<?php

namespace SV\UserMentionsImprovements\XF\Entity;

class UserGroup extends XFCP_UserGroup
{
	public function getIconHtml()
	{
		$innerContent = '<img src="' . $this->sv_avatar_s . '" '
			. ' alt="' . htmlspecialchars($this->title) . '"'
			. ' />';

		return "<span class=\"avatar avatar--xxs\">$innerContent</span>";
	}
}