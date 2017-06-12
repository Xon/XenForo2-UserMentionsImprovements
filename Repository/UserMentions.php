<?php

namespace SV\UserMentionsImprovements\Repository;

use XF\Entity\UserGroup;
use XF\Error;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;

class UserMentions extends Repository
{
	/**
	 * @param \XF\Entity\UserGroup $userGroup
	 *
	 * @return \XF\Mvc\Entity\ArrayCollection
	 */
	public function getMembersOfUserGroup(UserGroup $userGroup)
	{
		$users = $this->finder('XF:User')->where('UserGroupRelations.user_group_id', $userGroup->user_group_id);

		return $users->fetch();
	}
}