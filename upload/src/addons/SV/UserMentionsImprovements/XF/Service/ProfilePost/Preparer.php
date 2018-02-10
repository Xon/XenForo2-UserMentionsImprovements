<?php


namespace SV\UserMentionsImprovements\XF\Service\ProfilePost;

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
        if (isset($this->profilePost->User))
        {
            /** @var \SV\UserMentionsImprovements\XF\Entity\User $user */
            /** @noinspection PhpUndefinedFieldInspection */
            $user = $this->profilePost->User;

            if ($user->canMention($this->profilePost))
            {
                if ($user->canMentionUserGroup())
                {
                    /** @var \SV\UserMentionsImprovements\Repository\UserMentions $userMentionsRepo */
                    $userMentionsRepo = \XF::app()->repository('SV\UserMentionsImprovements:UserMentions');
                    $this->mentionedUsers = $userMentionsRepo->mergeUserGroupMembersIntoUsersArray($this->mentionedUsers, $processor->getMentionedUserGroups());
                }
            }
            else
            {
                $this->mentionedUsers = [];
            }
        }

        return $message;
    }
}
