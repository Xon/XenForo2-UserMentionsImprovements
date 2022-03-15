<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Service\ProfilePost;

use SV\UserMentionsImprovements\Str\ServiceUserGroupExtractor;

class Preparer extends XFCP_Preparer
{
    use ServiceUserGroupExtractor;

    /**@var \SV\UserMentionsImprovements\XF\Service\StructuredText\Preparer|\XF\Service\Message\Preparer */
    protected $processor;

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

        $user = $this->svGetUserEntity($this->profilePost, $this->profilePost->username ?: \XF::visitor()->username);
        if ($user->canMention($this->profilePost))
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
