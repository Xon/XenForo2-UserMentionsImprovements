<?php

namespace SV\UserMentionsImprovements\Entity;

use SV\UserMentionsImprovements\XF\Entity\User;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * @property int     user_id
 * @property int     user_group_id
 * @property boolean is_primary
 * @property User    User
 */
class UserGroupRelation extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_user_group_relation';
        $structure->primaryKey = ['user_id', 'user_group_id'];
        $structure->columns = [
            'user_id'       => [
                'type' => self::UINT,
            ],
            'user_group_id' => [
                'type' => self::UINT,
            ],
            'is_primary'    => [
                'type' => self::BOOL,
            ],
        ];
        $structure->relations = [
            'User' => [
                'entity'     => 'XF:User',
                'type'       => self::TO_MANY,
                'conditions' => 'user_id',
            ],
        ];

        return $structure;
    }
}
