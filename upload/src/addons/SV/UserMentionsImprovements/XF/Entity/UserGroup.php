<?php

namespace SV\UserMentionsImprovements\XF\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * @extends \XF\Entity\UserGroup
 * @property bool   $sv_mentionable
 * @property bool   $sv_private
 * @property int    $sv_avatar_edit_date
 * @property string $sv_avatar_s
 * @property string $sv_avatar_l
 * GETTER
 * @property-read string $sv_avatar_s_url
 * @property-read string $sv_avatar_l_url
 * @property-read string $icon_html
 */
class UserGroup extends XFCP_UserGroup
{
    protected function getSvAvatarSUrl(): string
    {
        if ($this->sv_avatar_s)
        {
            $val = $this->sv_avatar_s . '?c=' . $this->sv_avatar_edit_date;
        }
        else
        {
            $val = \XF::options()->sv_default_group_avatar_s ?? '';
        }
        $val = \trim($val);

        if (\strlen($val) === 0)
        {
            return false;
        }

        return $this->app()->templater()->func('base_url', [$val]);
    }

    protected function getSvAvatarLUrl(): string
    {
        if ($this->sv_avatar_l)
        {
            $val = $this->sv_avatar_l . '?c=' . $this->sv_avatar_edit_date;
        }
        else
        {
            $val = \XF::options()->sv_default_group_avatar_l ?? '';
        }
        $val = \trim($val);

        if (\strlen($val) === 0)
        {
            return false;
        }

        return $this->app()->templater()->func('base_url', [$val]);
    }

    public function getIconHtml(): string
    {
        $link = \XF::app()->router()->buildLink('members/usergroup', $this);
        /** @noinspection HtmlUnknownTarget */
        $image = \sprintf('<img src="%s" alt="%s" />', $this->sv_avatar_s_url, \htmlspecialchars($this->title));

        /** @noinspection HtmlUnknownTarget */
        return \sprintf('<a class="%s" href="%s">%s</a>', 'avatar avatar--xxs', $link, $image);
    }

    /** @noinspection PhpMissingReturnTypeInspection */
    public function canView()
    {
        if (!$this->sv_mentionable)
        {
            return false;
        }

        $visitor = \XF::visitor();
        if ($visitor->hasPermission('general', 'sv_ViewPrivateGroups'))
        {
            return true;
        }

        if (!$visitor->hasPermission('general', 'sv_ViewPublicGroups'))
        {
            return false;
        }

        if ($this->sv_private)
        {
            if ($visitor->isMemberOf($this->user_group_id))
            {
                return true;
            }

            return false;
        }

        return true;
    }

    public function canViewForAutocomplete(): bool
    {
        if (!$this->sv_mentionable)
        {
            return false;
        }

        $visitor = \XF::visitor();
        if ($visitor->hasPermission('general', 'sv_ViewPrivateGroups'))
        {
            return true;
        }

        if ($this->sv_private)
        {
            if ($visitor->isMemberOf($this->user_group_id))
            {
                return true;
            }

            return false;
        }

        return true;
    }

    protected function _preSave()
    {
        parent::_preSave();
        if ($this->isUpdate() && ($this->isChanged('sv_mentionable') || $this->isChanged('sv_private') || $this->isChanged('sv_avatar_s')) ||
            $this->isInsert() && ($this->get('sv_mentionable') && !$this->get('sv_private') && $this->get('sv_avatar_s'))
        )
        {
            $this->set('sv_avatar_edit_date', \XF::$time);
        }
    }

    /** @noinspection PhpMissingReturnTypeInspection */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['sv_mentionable'] = ['type' => Entity::BOOL, 'default' => false];
        $structure->columns['sv_private'] = ['type' => Entity::BOOL, 'default' => true];
        $structure->columns['sv_avatar_s'] = ['type' => Entity::STR, 'default' => null, 'nullable' => true];
        $structure->columns['sv_avatar_l'] = ['type' => Entity::STR, 'default' => null, 'nullable' => true];
        $structure->columns['sv_avatar_edit_date'] = ['type' => Entity::UINT, 'default' => \XF::$time];

        $structure->getters['sv_avatar_s_url'] = true;
        $structure->getters['sv_avatar_l_url'] = true;
        $structure->getters['icon_html'] = true;

        return $structure;
    }
}
