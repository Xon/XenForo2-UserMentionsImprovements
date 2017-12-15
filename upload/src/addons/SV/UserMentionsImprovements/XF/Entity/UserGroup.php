<?php

namespace SV\UserMentionsImprovements\XF\Entity;

class UserGroup extends XFCP_UserGroup
{
	public function getSvAvatarS()
	{
		if ($this->getValue('sv_avatar_s'))
		{
			$val = $this->getValue('sv_avatar_s') . '?c=' . $this->sv_avatar_edit_date;
		}
		else
		{
			$val = \XF::options()->sv_default_group_avatar_s;
		}

		if (!$val)
		{
			return false;
		}

		return $this->app()->templater()->fn('base_url', [$val]);
	}

	public function getSvAvatarL()
	{
		if ($this->getValue('sv_avatar_l'))
		{
			$val = $this->getValue('sv_avatar_l') . '?c=' . $this->sv_avatar_edit_date;
		}
		else
		{
			$val = \XF::options()->sv_default_group_avatar_l;
		}

		if (!$val)
		{
			return false;
		}

		return $this->app()->templater()->fn('base_url', [$val]);
	}

	public function getIconHtml()
	{
		$innerContent = '<img src="' . $this->sv_avatar_s . '" '
			. ' alt="' . htmlspecialchars($this->title) . '"'
			. ' />';

		return "<span class=\"avatar avatar--xxs\">$innerContent</span>";
	}

	public function canView()
	{
		return \XF::visitor()
				->hasPermission('general', 'sv_ViewPrivateGroups') || ($this->sv_mentionable && (!$this->sv_private || \XF::visitor()
						->isMemberOf($this->user_group_id)));
	}
}