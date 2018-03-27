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
    protected $mentionedUserGroups = [];

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
     *
     * @return bool
     */
    public function setMessage($message, $format = true, $checkValidity = true)
    {
        $valid = parent::setMessage($message, $format, $checkValidity);

        $preparer = $this->messagePreparer;
        $this->messagePreparer = null;
        $this->mentionedUserGroups = $preparer->getMentionedUserGroups();

        return $valid;
    }

    /**
     * @param bool $format
     *
     * @return \XF\Service\Message\Preparer
     */
    protected function getMessagePreparer($format = true)
    {
        $preparer = parent::getMessagePreparer($format);
        $this->messagePreparer = $preparer;

        return $preparer;
    }
}
