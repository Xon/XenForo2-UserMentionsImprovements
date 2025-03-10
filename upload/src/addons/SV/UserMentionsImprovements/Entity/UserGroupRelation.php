<?php

namespace SV\UserMentionsImprovements\Entity;

use SV\UserMentionsImprovements\XF\Entity\User as ExtendedUserEntity;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * @property int                          $user_id
 * @property int                          $user_group_id
 * @property bool                         $is_primary
 * @property-read ExtendedUserEntity|null $User
 */
class UserGroupRelation extends Entity
{
    /** @noinspection PhpMissingParentCallCommonInspection */
    public static function getStructure(Structure $structure): Structure
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
