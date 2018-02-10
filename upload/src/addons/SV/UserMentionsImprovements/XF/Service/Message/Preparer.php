<?php


namespace SV\UserMentionsImprovements\XF\Service\Message;

class Preparer extends XFCP_Preparer
{
    public function prepare($message, $checkValidity = true)
    {
        $message = parent::prepare($message, $checkValidity);

        /** @noinspection PhpUndefinedFieldInspection */
        if (isset($this->messageEntity->User))
        {
            /** @var \SV\UserMentionsImprovements\XF\Entity\User $user */
            /** @noinspection PhpUndefinedFieldInspection */
            $user = $this->messageEntity->User;

            if ($user->canMention($this->messageEntity))
            {
                if ($user->canMentionUserGroup())
                {
                    /** @var \SV\UserMentionsImprovements\XF\BbCode\ProcessorAction\MentionUsers|null $processor */
                    $processor = $this->bbCodeProcessor->getFilterer('mentions');
                    /** @var \SV\UserMentionsImprovements\Repository\UserMentions $userMentionsRepo */
                    $userMentionsRepo = \XF::app()->repository('SV\UserMentionsImprovements:UserMentions');
                    $this->mentionedUserGroups = $processor->getMentionedUserGroups();
                    $this->mentionedUsers = $userMentionsRepo->mergeUserGroupMembersIntoUsersArray($this->mentionedUsers, $this->mentionedUserGroups);
                }
            }
            else
            {
                $this->mentionedUserGroups = [];
                $this->mentionedUsers = [];
            }
        }

        return $message;
    }

    protected $mentionedUserGroups = [];

    public function getMentionedUserGroups()
    {
        return $this->mentionedUserGroups;
    }
}
