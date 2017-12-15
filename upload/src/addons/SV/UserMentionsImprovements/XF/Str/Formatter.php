<?php

namespace SV\UserMentionsImprovements\XF\Str;

class Formatter extends XFCP_Formatter
{
    /**
     * @return \SV\UserMentionsImprovements\Str\UserGroupMentionFormatter
     */
    public function getUserGroupMentionFormatter()
    {
        $class = \XF::extendClass('SV\UserMentionsImprovements\Str\UserGroupMentionFormatter');

        return new $class();
    }

    public function linkStructuredTextMentions($string)
    {
        $string = parent::linkStructuredTextMentions($string);
        $string = $this->moveHtmlToPlaceholders($string, $restorePlaceholders);

        $string = preg_replace_callback(
            '#(?<=^|\s|[\](,]|--|@)@UG\[(\d+):(\'|"|&quot;|)(.*)\\2\]#iU',
            function (array $match) {
                $userGroupId = intval($match[1]);
                $title = $this->removeHtmlPlaceholders($match[3]);
                $title = htmlspecialchars($title, ENT_QUOTES, 'utf-8', false);

                $link = \XF::app()->router()->buildLink('full:members/usergroup', ['user_group_id' => $userGroupId]);

                return sprintf(
                    '<a href="%s" class="usergroup">%s</a>',
                    htmlspecialchars($link), $title
                );
            },
            $string
        );

        $string = $restorePlaceholders($string);

        return $string;
    }
}
