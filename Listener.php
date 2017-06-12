<?php

namespace SV\UserMentionsImprovements;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Manager;
use XF\Mvc\Entity\Structure;

class Listener
{
	public static function usergroupEntityStructure(Manager $em, Structure &$structure)
	{
		$structure->columns['sv_mentionable'] = ['type' => Entity::BOOL, 'default' => 0];
		$structure->columns['sv_private'] = ['type' => Entity::BOOL, 'default' => 0];
		$structure->columns['sv_avatar_s'] = ['type' => Entity::STR, 'default' => null, 'nullable' => true];
		$structure->columns['sv_avatar_l'] = ['type' => Entity::STR, 'default' => null, 'nullable' => true];
		$structure->columns['sv_avatar_edit_date'] = ['type' => Entity::UINT, 'default' => \XF::$time];

		$structure->getters['sv_avatar_s'] = true;
		$structure->getters['sv_avatar_l'] = true;
	}

	public static function usergroupEntityPreSave(\XF\Mvc\Entity\Entity $entity)
	{
		if ($entity->isUpdate() && ($entity->isChanged('sv_mentionable') || $entity->isChanged('sv_private') || $entity->isChanged('sv_avatar_s')) ||
			$entity->isInsert() && ($entity->get('sv_mentionable') && !$entity->get('sv_private') && $entity->get('sv_avatar_s'))
		)
		{
			$entity->set('sv_avatar_edit_date', \XF::$time);
		}
	}

	public static function useroptionEntityStructure(Manager $em, Structure &$structure)
	{
		$structure->columns['sv_email_on_mention'] = ['type' => Entity::BOOL, 'default' => 0];
		$structure->columns['sv_email_on_quote'] = ['type' => Entity::BOOL, 'default' => 0];
	}

	public static function userEntityStructure(Manager $em, Structure &$structure)
	{
		$structure->relations['UserGroupRelations'] = [
			'entity' => 'SV\UserMentionsImprovements:UserGroupRelation',
			'type' => Entity::TO_MANY,
			'conditions' => 'user_id'
		];
	}
}