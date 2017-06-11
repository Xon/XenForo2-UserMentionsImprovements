<?php

namespace SV\UserMentionsImprovements\XF\Pub\Controller;

use XF\Mvc\Reply\View;

class Member extends XFCP_Member
{
	public function actionFind()
	{
		$response = parent::actionFind();

		if ($response instanceof View)
		{
			$q = ltrim($this->filter('q', 'str', ['no-trim']));

			if ($q !== '' && utf8_strlen($q) >= 2)
			{
				$userGroupFinder = $this->finder('XF:UserGroup');

				/** @var \XF\Mvc\Entity\AbstractCollection $userGroups */
				$userGroups = $userGroupFinder
					->where('title', 'like', $userGroupFinder->escapeLike($q, '?%'))
					->where('sv_mentionable', 1)
					->fetch();

				// TODO: Put this into the finder query if possible
				$userGroups->filter(function ($userGroup)
				{
					return !$userGroup->sv_private || \XF::visitor()->isMemberOf($userGroup->user_group_id);
				});
			}
			else
			{
				$userGroups = [];
			}

			$response->setParam('usergroups', $userGroups);
		}

		return $response;
	}
}