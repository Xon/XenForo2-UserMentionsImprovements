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
     * @param string $title
     * @return AbstractCollection
     */
    public function mentionableGroups($title)
    {
        return $this
            ->where('title', 'like', $this->escapeLike($title, '?%'))
            ->where('sv_mentionable', 1);
    }
}
