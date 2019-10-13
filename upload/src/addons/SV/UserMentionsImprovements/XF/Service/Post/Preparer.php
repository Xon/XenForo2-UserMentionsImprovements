<?php


namespace SV\UserMentionsImprovements\XF\Service\Post;

class Preparer extends XFCP_Preparer
{
    /**
     * @var \XF\Service\Message\Preparer
     */
    protected $messagePreparer;

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
