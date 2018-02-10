<?php

namespace SV\UserMentionsImprovements\XF\Finder;

use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\ArrayCollection;

/**
 * Extends \XF\Finder\UserGroup
 */
class UserGroup extends XFCP_UserGroup
{
    /**
     * @param string $q
     * @return AbstractCollection
     */
    public function getMentionableGroups($q)
    {
        return $this
            ->where('title', 'like', $this->escapeLike($q, '?%'))
            ->where('sv_mentionable', 1);
    }

    /**
     * @param AbstractCollection|\SV\UserMentionsImprovements\XF\Entity\UserGroup[] $userGroups
     * @return AbstractCollection
     */
    public function filterMentionableGroup($userGroups)
    {
        if (is_array($userGroups))
        {
            $userGroups = new ArrayCollection($userGroups);
        }
        /** @var AbstractCollection $userGroups */
        return $userGroups->filter(
            function ($userGroup) {
                return !$userGroup->sv_private || \XF::visitor()->isMemberOf($userGroup->user_group_id);
            }
        );
    }
}
