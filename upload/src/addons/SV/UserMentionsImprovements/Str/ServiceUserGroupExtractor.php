<?php

namespace SV\UserMentionsImprovements\Str;

use SV\UserMentionsImprovements\XF\Entity\User;

trait ServiceUserGroupExtractor
{
    /** @var array */
    protected $implicitMentionedUsers = [];
    /** @var array */
    protected $explicitMentionedUsers = [];
    /** @var array */
    protected $mentionedUserGroups = [];

    /**
     * @param \XF\Service\Message\Preparer|ServiceUserGroupExtractorInterface|null $preparer
     * @return void
     */
    public function svCopyFields(\XF\Service\Message\Preparer $preparer = null)
    {
        if ($preparer instanceof ServiceUserGroupExtractorInterface)
        {
            $this->implicitMentionedUsers = $preparer->getImplicitMentionedUsers();
            $this->explicitMentionedUsers = $preparer->getExplicitMentionedUsers();
            $this->mentionedUserGroups = $preparer->getMentionedUserGroups();
        }
    }

    /**
     * @param \XF\Mvc\Entity\Entity $content
     * @param string                $username
     * @return User
     */
    protected function svGetUserEntity(\XF\Mvc\Entity\Entity $content = null, string $username = null): User
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
}