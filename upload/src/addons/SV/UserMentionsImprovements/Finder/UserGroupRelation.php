<?php

namespace SV\UserMentionsImprovements\Finder;

use SV\StandardLib\Helper;
use XF\Mvc\Entity\AbstractCollection as AbstractCollection;
use XF\Mvc\Entity\Finder as Finder;
use SV\UserMentionsImprovements\Entity\UserGroupRelation as UserGroupRelationEntity;

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
