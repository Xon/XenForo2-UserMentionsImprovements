<?php

namespace SV\UserMentionsImprovements\XF\Str;

use SV\UserMentionsImprovements\Str\UserGroupMentionFormatter;
use SV\UserMentionsImprovements\XF\BbCode\ProcessorAction\MentionUsers;
use function htmlspecialchars;
use function preg_replace_callback;
use function sprintf;

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

    protected function linkStructuredUserGroupMentions($string): string
    {
        /** @noinspection RegExpRedundantEscape */
        return preg_replace_callback('#(?<=^|\s|[\](,]|--|@)@UG\[(\d+):(\'|"|&quot;|)(.*)\\2\]#iU',
            function (array $match): string {
                $userGroupId = (int)$match[1];
                $title = $this->removeHtmlPlaceholders($match[3]);
                $title = htmlspecialchars($title, ENT_QUOTES, 'utf-8', false);

                $link = \XF::app()->router()->buildLink('full:members/usergroup', ['user_group_id' => $userGroupId]);

                /** @noinspection HtmlUnknownTarget */
                return sprintf('<a href="%s" class="usergroup">%s</a>', htmlspecialchars($link), $title);
            }, $string);
    }
}
