<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Service\Post;

use SV\UserMentionsImprovements\Str\ServiceUserGroupExtractor;
use SV\UserMentionsImprovements\Str\ServiceUserGroupExtractorInterface;

class Preparer extends XFCP_Preparer implements ServiceUserGroupExtractorInterface
{
    use ServiceUserGroupExtractor;

    /**@var \XF\Service\Message\Preparer|null */
    protected $svPreparer = null;

    /**
     * @param string $message
     * @param bool   $format
     * @param bool   $checkValidity
     * @return bool
     */
    public function setMessage($message, $format = true, $checkValidity = true)
    {
        $valid = parent::setMessage($message, $format, $checkValidity);

        $this->svCopyFields($this->svPreparer);

        return $valid;
    }

    /**
     * @param bool $format
     * @return \XF\Service\Message\Preparer
     */
    protected function getMessagePreparer($format = true)
    {
        $preparer = parent::getMessagePreparer($format);
        $this->svPreparer = $preparer;

        return $preparer;
    }
}
