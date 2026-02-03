<?php

namespace SV\UserMentionsImprovements\Finder;

use SV\StandardLib\Helper;
use SV\UserMentionsImprovements\Entity\UserGroupRelation as UserGroupRelationEntity;
use XF\Mvc\Entity\AbstractCollection as AbstractCollection;
use XF\Mvc\Entity\Finder as Finder;

/**
 * @method AbstractCollection<UserGroupRelationEntity>|UserGroupRelationEntity[] fetch(?int $limit = null, ?int $offset = null)
 * @method UserGroupRelationEntity|null fetchOne(?int $offset = null)
 * @implements \IteratorAggregate<string|int,UserGroupRelationEntity>
 * @extends Finder<UserGroupRelationEntity>
 */
class UserGroupRelation extends Finder
{
    /**
     * @return static
     */
    public static function finder(): self
    {
        return Helper::finder(self::class);
    }
}
