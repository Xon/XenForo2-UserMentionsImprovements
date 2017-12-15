<?php

namespace SV\UserMentionsImprovements\XF\BbCode\ProcessorAction;

class MentionUsers extends XFCP_MentionUsers
{
    protected $mentionedUserGroups = [];

    public function filterFinal($string)
    {
        $string = parent::filterFinal($string);

        /** @var \SV\UserMentionsImprovements\XF\Str\Formatter $formatter */
        $formatter = $this->formatter;
        /** @var \SV\UserMentionsImprovements\Str\UserGroupMentionFormatter $userGroupMentions */
        $userGroupMentions = $formatter->getUserGroupMentionFormatter();

        $string = $userGroupMentions->getMentionsBbCode($string);
        $this->mentionedUserGroups = $userGroupMentions->getMentionedUserGroups();

        return $string;
    }

    public function getMentionedUserGroups()
    {
        return $this->mentionedUserGroups;
    }
}
