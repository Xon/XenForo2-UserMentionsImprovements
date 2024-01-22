<?php

namespace SV\UserMentionsImprovements\Str;


use XF\Service\Message\Preparer as MsgPreparer;

interface ServiceUserGroupExtractorInterface
{
    /**
     * @param MsgPreparer|ServiceUserGroupExtractorInterface|null $preparer
     * @return void
     */
    public function svCopyFields(MsgPreparer $preparer = null);

    public function getImplicitMentionedUsers(): array;

    public function getImplicitMentionedUserIds(): array;

    public function getExplicitMentionedUsers(): array;

    public function getExplicitMentionedUserIds(): array;

    public function getMentionedUserGroups(): array;

    public function getMentionedUserGroupIds(): array;
}