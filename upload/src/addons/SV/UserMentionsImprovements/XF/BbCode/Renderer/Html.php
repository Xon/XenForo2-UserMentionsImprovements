<?php

namespace SV\UserMentionsImprovements\XF\BbCode\Renderer;

class Html extends XFCP_Html
{
    public function addDefaultTags()
    {
        parent::addDefaultTags();

        $this->addTag(
            'usergroup', [
            'callback' => 'renderTagUserGroup'
        ]
        );
    }

    static $groupAvatar = null;
    static $groupUsername = null;

    public function renderTagUserGroup(array $children, $option, array $tag, array $options)
    {
        $content = $this->renderSubTree($children, $options);
        if ($content === '')
        {
            return '';
        }

        $userGroupId = intval($option);
        if ($userGroupId <= 0)
        {
            return $content;
        }

        $link = \XF::app()->router('public')->buildLink('full:members/usergroup', ['user_group_id' => $userGroupId]);
        if (self::$groupAvatar === null)
        {
            $options = \XF::app()->options();
            self::$groupUsername = $options->sv_styleGroupUsername
                ? 'username'
                : '';
            self::$groupAvatar = $options->sv_displayGroupAvatar
                ? '<span class="groupImg"></span>'
                : '';
        }

        return $this->wrapHtml(
            '<a href="' . htmlspecialchars($link) . '" class="'.self::$groupUsername.' ug" data-usergroup="' . $userGroupId . ', ' . htmlspecialchars($content) . '"><span class="style'.$userGroupId.'">'.self::$groupAvatar,
            $content,
            '</span></a>'
        );
    }
}
