<?php

namespace SV\UserMentionsImprovements\XF\Str;

use SV\UserMentionsImprovements\bbCode\tagRenderer as UserGroupTagRenderer;
use SV\UserMentionsImprovements\Str\UserGroupMentionFormatter;
use SV\UserMentionsImprovements\XF\BbCode\ProcessorAction\MentionUsers;
use function htmlspecialchars;
use function preg_replace_callback;

/**
 * @extends \XF\Str\Formatter
 */
class Formatter extends XFCP_Formatter
{
    /** @var MentionUsers */
    public $svMentionUserGroup = null;

    /** @noinspection PhpMissingReturnTypeInspection */
    public function getMentionFormatter()
    {
        /** @var MentionFormatter $mentions */
        $mentions = parent::getMentionFormatter();
        if ($this->svMentionUserGroup)
        {
            $mentions->svMentionUserGroup = $this->svMentionUserGroup;
        }

        return $mentions;
    }

    public function getUserGroupMentionFormatter(): UserGroupMentionFormatter
    {
        $class = \XF::extendClass(UserGroupMentionFormatter::class);

        return new $class();
    }

    /**
     * @param $string
     * @return null|string
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function linkStructuredTextMentions($string)
    {
        $string = parent::linkStructuredTextMentions($string);
        if ($this->canViewPublicGroups())
        {
            $string = $this->moveHtmlToPlaceholders($string, $restorePlaceholders);
            $string = $this->linkStructuredUserGroupMentions($string);
            $string = $restorePlaceholders($string);
        }

        return $string;
    }

    protected function canViewPublicGroups(): bool
    {
        $visitor = \XF::visitor();

        return (\XF::options()->svUMIPermDeniedOnViewGroup ?? true) ||
               $visitor->hasPermission('general', 'sv_ViewPrivateGroups') ||
               $visitor->hasPermission('general', 'sv_ViewPublicGroups');
    }

    /** @noinspection SpellCheckingInspection */
    protected function linkStructuredUserGroupMentions($string): string
    {
        $class = \XF::app()->extendClass(UserGroupTagRenderer::class);
        /** @var callable(int $groupId,string $css,string $link,string $groupName, string $title):string $userGroupTemplate */
        $userGroupTemplate = \Closure::fromCallable([$class, 'renderTagUserGroupTemplate']);

        /** @noinspection RegExpRedundantEscape */
        return preg_replace_callback('#(?<=^|\s|[\](,]|--|@)@UG\[(\d+):(\'|"|&quot;|)(.*)\\2\]#iU',
            function (array $match) use ($userGroupTemplate): string {
                $userGroupId = (int)$match[1];
                $title = $this->removeHtmlPlaceholders($match[3]);
                $title = htmlspecialchars($title, ENT_QUOTES, 'utf-8', false);

                $link = \XF::app()->router()->buildLink('full:members/usergroup', ['user_group_id' => $userGroupId]);
                $link = htmlspecialchars($link);

                return $userGroupTemplate($userGroupId, 'ug', $link, $title, $title);
            }, $string);
    }
}
