<?php

namespace SV\UserMentionsImprovements\XF\Finder;

/**
 * Extends \XF\Finder\UserGroup
 */
class UserGroup extends XFCP_UserGroup
{
    /**
     * @param string $title
     * @return $this
     */
    public function mentionableGroups($title)
    {
        $this->where('title', 'like', $this->escapeLike($title, '?%'))
             ->where('sv_mentionable', 1);

        return $this;
    }
}
