<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Service\Post;

use SV\UserMentionsImprovements\Str\ServiceUserGroupExtractor;

class Preparer extends XFCP_Preparer
{
    use ServiceUserGroupExtractor;

    /**  @var \XF\Service\Message\Preparer|null */
    protected $messagePreparer;

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
