<?php

namespace SV\UserMentionsImprovements\Str;


interface ServiceUserGroupExtractorInterface
{
    /**
     * @param \XF\Service\Message\Preparer|ServiceUserGroupExtractorInterface|null $preparer
     * @return void
     */
    public function svCopyFields(\XF\Service\Message\Preparer $preparer = null);

    function getImplicitMentionedUsers(): array;
    function getImplicitMentionedUserIds(): array;
    function getExplicitMentionedUsers(): array;
    function getExplicitMentionedUserIds(): array;
    function getMentionedUserGroups(): array;
    function getMentionedUserGroupIds(): array;
}