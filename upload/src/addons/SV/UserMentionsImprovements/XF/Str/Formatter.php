<?php

namespace SV\UserMentionsImprovements\XF\Str;

use SV\UserMentionsImprovements\Str\UserGroupMentionFormatter;
use SV\UserMentionsImprovements\XF\BbCode\ProcessorAction\MentionUsers;

/**
 * @extends \XF\Str\Formatter
 */
class Formatter extends XFCP_Formatter
{
    /** @var MentionUsers  */
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
     * @noinspection PhpUnnecessaryLocalVariableInspection
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function linkStructuredTextMentions($string)
    {
        $string = parent::linkStructuredTextMentions($string);
        $string = $this->moveHtmlToPlaceholders($string, $restorePlaceholders);

        /** @noinspection RegExpRedundantEscape */
        $string = \preg_replace_callback(
            '#(?<=^|\s|[\](,]|--|@)@UG\[(\d+):(\'|"|&quot;|)(.*)\\2\]#iU',
            function (array $match) {
                $userGroupId = \intval($match[1]);
                $title = $this->removeHtmlPlaceholders($match[3]);
                $title = \htmlspecialchars($title, ENT_QUOTES, 'utf-8', false);

                $link = \XF::app()->router()->buildLink('full:members/usergroup', ['user_group_id' => $userGroupId]);

                /** @noinspection HtmlUnknownTarget */
                return \sprintf(
                    '<a href="%s" class="usergroup">%s</a>',
                    \htmlspecialchars($link), $title
                );
            },
            $string
        );

        $string = $restorePlaceholders($string);

        return $string;
    }
}
