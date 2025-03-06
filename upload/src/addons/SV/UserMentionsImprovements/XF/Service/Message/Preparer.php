<?php

namespace SV\UserMentionsImprovements\XF\Service\Message;

use SV\UserMentionsImprovements\Repository\UserMentions as UserMentionsRepo;
use SV\UserMentionsImprovements\Str\ServiceUserGroupExtractor;
use SV\UserMentionsImprovements\Str\ServiceUserGroupExtractorInterface;
use SV\UserMentionsImprovements\XF\BbCode\ProcessorAction\MentionUsers;

/**
 * @extends \XF\Service\Message\Preparer
 */
class Preparer extends XFCP_Preparer implements ServiceUserGroupExtractorInterface
{
    use ServiceUserGroupExtractor;

    /**
     * @param string $message
     * @param bool   $checkValidity
     * @return string
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function prepare($message, $checkValidity = true)
    {
        // skip doing checks
        if ($message === '' || $message === null)
        {
            return parent::prepare($message, $checkValidity);
        }

        $user = $this->svGetUserEntity($this->messageEntity,
            ($this->messageEntity && $this->messageEntity->offsetExists('username')
                ? $this->messageEntity->get('username')
                : null) ?: \XF::visitor()->username
        );

        $canMention = $user->canMention($this->messageEntity);
        if (!$canMention && (\XF::options()->svBlockMentionRenderingOnNoPermissions ?? true))
        {
            $this->filters['mentions'] = false;
        }
        $message = parent::prepare($message, $checkValidity);

        /** @var MentionUsers|null $processor */
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
                $this->mentionedUserGroups = $processor->getMentionedUserGroups();
                $this->mentionedUsers = UserMentionsRepo::get()->mergeUserGroupMembersIntoUsersArray(
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
}
