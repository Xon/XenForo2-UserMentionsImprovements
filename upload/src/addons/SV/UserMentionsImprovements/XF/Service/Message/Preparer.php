<?php

namespace SV\UserMentionsImprovements\XF\Service\Message;

use SV\UserMentionsImprovements\Str\ServiceUserGroupExtractor;

class Preparer extends XFCP_Preparer
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
}
