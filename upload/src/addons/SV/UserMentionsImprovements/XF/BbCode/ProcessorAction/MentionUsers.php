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

    public function getMentionedUsers()
    {
        $users = parent::getMentionedUsers();

        /** @var \SV\UserMentionsImprovements\XF\Entity\User $visitor */
        $visitor = \XF::visitor();
        if (!$visitor->canMentionUserGroup())
        {
            return $users;
        }

        /** @var \SV\UserMentionsImprovements\Repository\UserMentions $userMentionsRepo */
        $userMentionsRepo = \XF::app()->repository('SV\UserMentionsImprovements:UserMentions');
        $users = $userMentionsRepo->mergeUserGroupMembersIntoUsersArray($users, $this->getMentionedUserGroups());

        return $users;
    }
}
