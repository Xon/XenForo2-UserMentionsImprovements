<?php

namespace SV\UserMentionsImprovements\Str;

use SV\StandardLib\Helper;
use SV\UserMentionsImprovements\XF\Entity\User as ExtendedUserEntity;
use XF\Mvc\Entity\Entity;
use XF\Repository\User as UserRepo;
use XF\Service\Message\Preparer as MsgPreparer;
use function array_keys;

trait ServiceUserGroupExtractor
{
    /** @var array */
    protected $implicitMentionedUsers = [];
    /** @var array */
    protected $explicitMentionedUsers = [];
    /** @var array */
    protected $mentionedUserGroups = [];

    /**
     * @param MsgPreparer|ServiceUserGroupExtractorInterface|null $preparer
     * @return void
     */
    public function svCopyFields(?MsgPreparer $preparer = null)
    {
        if ($preparer instanceof ServiceUserGroupExtractorInterface)
        {
            // These variables are not directly used in this add-on, but SV/UserEssentials does call these functions to get these values.
            // However, the actual user-groups are pushed via
            // \SV\UserMentionsImprovements\Globals::$userGroupMentionedIds due to the disconnect between mention extraction and mention alerting
            $this->implicitMentionedUsers = $preparer->getImplicitMentionedUsers();
            $this->explicitMentionedUsers = $preparer->getExplicitMentionedUsers();
            $this->mentionedUserGroups = $preparer->getMentionedUserGroups();
        }
    }

    /**
     * @param Entity|null $content
     * @param string|null $username
     * @return ExtendedUserEntity
     */
    protected function svGetUserEntity(?Entity $content = null, ?string $username = null): ExtendedUserEntity
    {
        /** @var ExtendedUserEntity|null $user */
        $user = Helper::repo()->getUserEntity($content);

        if (!$user)
        {
            $userRepo = Helper::repository(UserRepo::class);
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
        return array_keys($this->getImplicitMentionedUsers());
    }

    public function getExplicitMentionedUsers(): array
    {
        return $this->explicitMentionedUsers;
    }

    public function getExplicitMentionedUserIds(): array
    {
        return array_keys($this->getExplicitMentionedUsers());
    }

    public function getMentionedUserGroups(): array
    {
        return $this->mentionedUserGroups;
    }

    public function getMentionedUserGroupIds(): array
    {
        return array_keys($this->getMentionedUserGroups());
    }
}