<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Service\ProfilePostComment;

use SV\UserMentionsImprovements\XF\Entity\User;

class Preparer extends XFCP_Preparer
{
    /** @var \SV\UserMentionsImprovements\XF\Service\StructuredText\Preparer|\XF\Service\Message\Preparer  */
    protected $processor;
    /** @var array */
    protected $implicitMentionedUsers = [];
    /** @var array */
    protected $explicitMentionedUsers = [];
    /** @var array */
    protected $mentionedUserGroups = [];

    public function getImplicitMentionedUsers(): array
    {
        return $this->implicitMentionedUsers;
    }

    public function getImplicitMentionedUserIds(): array
    {
        return \array_keys($this->getImplicitMentionedUsers());
    }

    public function getExplicitMentionedUsers(): array
    {
        return $this->explicitMentionedUsers;
    }

    public function getExplicitMentionedUserIds(): array
    {
        return \array_keys($this->getExplicitMentionedUsers());
    }

    public function getMentionedUserGroups(): array
    {
        return $this->mentionedUserGroups;
    }

    public function getMentionedUserGroupIds(): array
    {
        return \array_keys($this->getMentionedUserGroups());
    }

    /**
     * @param \XF\Mvc\Entity\Entity $content
     * @param string                $username
     * @return User
     */
    protected function svGetUserEntity(\XF\Mvc\Entity\Entity $content, string $username = null): User
    {
        /** @var User $user */
        $user = \SV\StandardLib\Helper::repo()->getUserEntity($content);

        if (!$user)
        {
            /** @var \XF\Repository\User $userRepo */
            $userRepo = $this->repository('XF:User');
            $user = $userRepo->getGuestUser($username);
        }

        return $user;
    }

    /**
     * @param string $message
     * @param bool   $format
     * @return bool
     */
    public function setMessage($message, $format = true)
    {
        $retval = parent::setMessage($message, $format);
        $processor = $this->processor;
        if (!$processor)
        {
            // mentions are just not enabled
            return $retval;
        }

        $user = $this->svGetUserEntity($this->comment, $this->comment->username ?: \XF::visitor()->username);
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

        return $retval;
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
