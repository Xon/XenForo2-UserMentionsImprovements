<?php


namespace SV\UserMentionsImprovements\XF\Service\ProfilePostComment;

class Preparer extends XFCP_Preparer
{
    /** @var \SV\UserMentionsImprovements\XF\Service\StructuredText\Preparer|null */
    protected $processor = null;

    protected function getStructuredTextPreparer($format = true)
    {
        $this->processor = parent::getStructuredTextPreparer($format);

        return $this->processor;
    }

    public function setMessage($message, $format = true)
    {
        $message = parent::setMessage($message, $format);
        $processor = $this->processor;
        $this->processor = null;

        /** @noinspection PhpUndefinedFieldInspection */
        if (isset($this->comment->User))
        {
            /** @var \SV\UserMentionsImprovements\XF\Entity\User $user */
            /** @noinspection PhpUndefinedFieldInspection */
            $user = $this->comment->User;

            if ($user->canMention($this->comment))
            {
                if ($user->canMentionUserGroup())
                {
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
