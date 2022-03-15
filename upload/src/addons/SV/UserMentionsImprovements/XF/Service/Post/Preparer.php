<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Service\Post;

class Preparer extends XFCP_Preparer
{
    /**  @var \XF\Service\Message\Preparer|null */
    protected $messagePreparer;
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
     * @param string $message
     * @param bool   $format
     * @param bool   $checkValidity
     * @return bool
     */
    public function setMessage($message, $format = true, $checkValidity = true)
    {
        $valid = parent::setMessage($message, $format, $checkValidity);
        /** @var Preparer $preparer */
        $preparer = $this->messagePreparer;
        if (!$preparer)
        {
            // mentions are just not enabled
            return $valid;
        }
        $this->implicitMentionedUsers = $preparer->getImplicitMentionedUsers();
        $this->explicitMentionedUsers = $preparer->getExplicitMentionedUsers();
        $this->mentionedUserGroups = $preparer->getMentionedUserGroups();

        return $valid;
    }

    /**
     * @param bool $format
     * @return \XF\Service\Message\Preparer
     */
    protected function getMessagePreparer($format = true)
    {
        $preparer = parent::getMessagePreparer($format);
        $this->messagePreparer = $preparer;

        return $preparer;
    }
}
