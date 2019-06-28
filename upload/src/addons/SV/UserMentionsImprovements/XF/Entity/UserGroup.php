<?php

namespace SV\UserMentionsImprovements\XF\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * @property bool sv_mentionable
 * @property bool sv_private
 * @property int  sv_avatar_edit_date
 *
 * @property string sv_avatar_s
 * @property string sv_avatar_l
 * @property string icon_html
 */
class UserGroup extends XFCP_UserGroup
{
    public function getSvAvatarS()
    {
        if ($this->getValue('sv_avatar_s'))
        {
            $val = $this->getValue('sv_avatar_s') . '?c=' . $this->sv_avatar_edit_date;
        }
        else
        {
            $val = \XF::options()->sv_default_group_avatar_s;
        }

        if (!$val)
        {
            return false;
        }

        $func = \XF::$versionId >= 2010370 ? 'func' : 'fn';
        return $this->app()->templater()->$func('base_url', [$val]);
    }

    public function getSvAvatarL()
    {
        if ($this->getValue('sv_avatar_l'))
        {
            $val = $this->getValue('sv_avatar_l') . '?c=' . $this->sv_avatar_edit_date;
        }
        else
        {
            $val = \XF::options()->sv_default_group_avatar_l;
        }

        if (!$val)
        {
            return false;
        }

        $func = \XF::$versionId >= 2010370 ? 'func' : 'fn';
        return $this->app()->templater()->$func('base_url', [$val]);
    }

    public function getIconHtml()
    {
        $link = \XF::app()->router()->buildLink('members/usergroup', $this);
        /** @noinspection HtmlUnknownTarget */
        $image = sprintf(
            '<img src="%s" alt="%s" />',
            $this->sv_avatar_s,
            htmlspecialchars($this->title)
        );

        /** @noinspection HtmlUnknownTarget */
        return sprintf(
            '<a class="%s" href="%s">%s</a>',
            'avatar avatar--xxs',
            $link,
            $image
        );
    }

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

        if ($this->sv_private && \XF::visitor()->isMemberOf($this->user_group_id))
        {
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

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['sv_mentionable'] = ['type' => Entity::BOOL, 'default' => 0];
        $structure->columns['sv_private'] = ['type' => Entity::BOOL, 'default' => 0];
        $structure->columns['sv_avatar_s'] = ['type' => Entity::STR, 'default' => null, 'nullable' => true];
        $structure->columns['sv_avatar_l'] = ['type' => Entity::STR, 'default' => null, 'nullable' => true];
        $structure->columns['sv_avatar_edit_date'] = ['type' => Entity::UINT, 'default' => \XF::$time];

        $structure->getters['sv_avatar_s'] = true;
        $structure->getters['sv_avatar_l'] = true;
        $structure->getters['icon_html'] = true;

        return $structure;
    }
}
