<?php

namespace SV\UserMentionsImprovements\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

class Member extends XFCP_Member
{
	public function actionUsergroup(ParameterBag $params)
	{
		$userGroupId = $params['user_group_id'];

		$userGroup = $this->assertRecordExists('XF:UserGroup', $userGroupId);

		if (!$userGroup->canView())
		{
			return $this->noPermission();
		}

		$users = $this->repository('SV\UserMentionsImprovements:UserMentions')->getMembersOfUserGroup($userGroup);

		$viewParams = [
			'users' => $users,
			'userGroup' => $userGroup
		];

		return $this->view('SV\UserMentionsImprovements:Member\UserGroup', 'sv_usermentionsimprovements_usergroup_view', $viewParams);
	}

	public function actionFind()
	{
		$response = parent::actionFind();

		if ($response instanceof View && \XF::visitor()->canMentionUserGroup())
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
		else if ($response instanceof View)
		{
			$response->setParam('usergroups', []);
		}

		return $response;
	}
}