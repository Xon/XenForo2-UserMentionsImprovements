<?php

namespace SV\UserMentionsImprovements\Repository;

use XF\Entity\UserGroup;
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
		$memberUserIds = \XF::db()->fetchAllColumn('
			SELECT DISTINCT user.user_id
			FROM xf_user AS user
			LEFT JOIN xf_user_group_relation AS relation ON relation.user_id = user.user_id
            WHERE relation.user_group_id = ?', $userGroup->user_group_id);

		$users = $this->finder('XF:User')->whereIds($memberUserIds)->fetch();

		return $users;
	}
}