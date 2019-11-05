<?php


namespace SV\UserMentionsImprovements\XF\Service\ProfilePostComment;

class Preparer extends XFCP_Preparer
{
    /**
     * @var \SV\UserMentionsImprovements\XF\Service\StructuredText\Preparer|\XF\Service\Message\Preparer
     */
    protected $processor;

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
     * @return array
     */
    public function getImplicitMentionedUsers()
    {
        return $this->implicitMentionedUsers;
    }

    /**
     * @return array
     */
    public function getImplicitMentionedUserIds()
    {
        return array_keys($this->getImplicitMentionedUsers());
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
    public function getExplicitMentionedUserIds()
    {
        return array_keys($this->getExplicitMentionedUsers());
    }

    /**
     * @return array
     */
    public function getMentionedUserGroups()
    {
        return $this->mentionedUserGroups;
    }

    /**
     * @return array
     */
    public function getMentionedUserGroupIds()
    {
        return array_keys($this->getMentionedUserGroups());
    }

    /**
     * @return \SV\UserMentionsImprovements\XF\Entity\User
     */
    protected function svGetUserEntity()
    {
        $user = null;
        if ($this->comment->isValidRelation('User'))
        {
            $user = $this->comment->getRelation('User');
        }

        if (!$user)
        {
            $user = $this->repository('XF:User')->getGuestUser();
        }

        return $user;
    }


    /**
     * @param string $message
     * @param bool   $format
     * @return string
     */
    public function setMessage($message, $format = true)
    {
        $message = parent::setMessage($message, $format);
        $processor = $this->processor;
        if (!$processor)
        {
            // mentions are just not enabled
            return $message;
        }

        $user = $this->svGetUserEntity();
        if ($user->canMention($this->comment))
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
     * @param bool $format
     * @return \XF\Service\Message\Preparer
     */
    protected function getMessagePreparer($format = true)
    {
        $preparer = parent::getMessagePreparer($format);
        $this->processor = $preparer;

        return $preparer;
    }
}
