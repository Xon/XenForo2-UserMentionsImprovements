<?php


namespace SV\UserMentionsImprovements\XF\Service\Message;

class Preparer extends XFCP_Preparer
{
    /**
     * @var array
     */
    protected $explicitMentionedUsers = [];

    /**
     * @var array
     */
    protected $mentionedUserGroups = [];

    /**
     * @param string $message
     * @param bool   $checkValidity
     *
     * @return string
     */
    public function prepare($message, $checkValidity = true)
    {
        $message = parent::prepare($message, $checkValidity);

        /** @var \SV\UserMentionsImprovements\XF\BbCode\ProcessorAction\MentionUsers|null $processor */
        $processor = $this->bbCodeProcessor->getFilterer('mentions');
        if (!$processor)
        {
            // mentions are just not enabled
            return $message;
        }

        /** @var \SV\UserMentionsImprovements\XF\Entity\User $user */
        $user = \XF::visitor();
        if ($user->canMention($this->messageEntity))
        {
            $this->explicitMentionedUsers = $this->mentionedUsers;

            if ($user->canMentionUserGroup())
            {
                /** @var \SV\UserMentionsImprovements\Repository\UserMentions $userMentionsRepo */
                $userMentionsRepo = \XF::app()->repository('SV\UserMentionsImprovements:UserMentions');
                $this->mentionedUserGroups = $processor->getMentionedUserGroups();
                $this->mentionedUsers = $userMentionsRepo->mergeUserGroupMembersIntoUsersArray(
                    $this->mentionedUsers,
                    $this->mentionedUserGroups
                );
            }
        }
        else
        {
            $this->mentionedUsers = [];
            $this->explicitMentionedUsers = [];
            $this->mentionedUserGroups = [];
        }

        return $message;
    }

    /**
     * @return array
     */
    public function getExplicitMentionedUsers()
    {
        return $this->explicitMentionedUsers;
    }

    /**
     * @return array
     */
    public function getMentionedUserGroups()
    {
        return $this->mentionedUserGroups;
    }
}
