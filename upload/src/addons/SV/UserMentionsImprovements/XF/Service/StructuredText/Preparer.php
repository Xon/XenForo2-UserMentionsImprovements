<?php

namespace SV\UserMentionsImprovements\XF\Service\StructuredText;

class Preparer extends XFCP_Preparer
{
    protected $mentionedUserGroups = [];

    protected function filterFinalUserMentions($null, $string)
    {
        $string = parent::filterFinalUserMentions($null, $string);

        /** @var \SV\UserMentionsImprovements\XF\Str\Formatter $formatter */
        $formatter = $this->app->stringFormatter();
        /** @var \SV\UserMentionsImprovements\Str\UserGroupMentionFormatter $mentions */
        $mentions = $formatter->getUserGroupMentionFormatter();

        $string = $mentions->getMentionsStructuredText($string);
        $this->mentionedUserGroups = $mentions->getMentionedUserGroups();

        return $string;
    }

    public function getMentionedUserGroups()
    {
        return $this->mentionedUserGroups;
    }

    public function getMentionedUsers()
    {
        $users = parent::getMentionedUsers();

        /** @noinspection PhpUndefinedFieldInspection */
        if (isset($this->messageEntity->User))
        {
            /** @var \SV\UserMentionsImprovements\XF\Entity\User $user */
            /** @noinspection PhpUndefinedFieldInspection */
            $user = $this->messageEntity->User;

            if (!$user->canMention($this->messageEntity))
            {
                $this->mentionedUsers = [];
            }
        }
        /** @var \SV\UserMentionsImprovements\XF\Entity\User $visitor */
        $visitor = \XF::visitor();

        if (!$visitor->canMention(null))
        {
            return [];
        }

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
