<?php


namespace SV\UserMentionsImprovements\XF\Service\Message;

class Preparer extends XFCP_Preparer
{
    /**
     * @var array
     */
    protected $implicitMentionedUsers = [];

    /**
     * @var array
     */
    protected $explicitMentionedUsers = [];

    /**
     * @var array
     */
    protected $mentionedUserGroups = [];

    /**
     * @return \SV\UserMentionsImprovements\XF\Entity\User
     */
    protected function svGetUserEntity()
    {
        $user = null;
        if ($this->messageEntity->isValidRelation('User'))
        {
            $user = $this->messageEntity->getRelation('User');
        }

        if (!$user)
        {
            $user = $this->repository('XF:User')->getGuestUser();
        }

        return $user;
    }

    /**
     * @param string $message
     * @param bool   $checkValidity
     * @return string
     */
    public function prepare($message, $checkValidity = true)
    {
        $user = $this->svGetUserEntity();

        $canMention = $user->canMention($this->messageEntity);
        if (!$canMention && \XF::options()->svBlockMentionRenderingOnNoPermissions)
        {
            $this->filters['mentions'] = false;
        }
        $message = parent::prepare($message, $checkValidity);

        /** @var \SV\UserMentionsImprovements\XF\BbCode\ProcessorAction\MentionUsers|null $processor */
        $processor = $this->bbCodeProcessor->getFilterer('mentions');
        if (!$processor)
        {
            // mentions are just not enabled
            return $message;
        }

        if ($canMention)
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
                $this->implicitMentionedUsers = array_diff_key(
                    $this->mentionedUsers,
                    $this->explicitMentionedUsers
                );
            }
        }
        else
        {
            $this->mentionedUsers = [];
            $this->implicitMentionedUsers = [];
            $this->explicitMentionedUsers = [];
            $this->mentionedUserGroups = [];
        }

        return $message;
    }

    /**
     * @return array
     */
    public function getImplicitMentionedUsers()
    {
        return $this->implicitMentionedUsers;
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
