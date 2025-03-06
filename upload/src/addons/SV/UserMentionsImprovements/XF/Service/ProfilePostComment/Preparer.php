<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Service\ProfilePostComment;

use SV\UserMentionsImprovements\Str\ServiceUserGroupExtractor;
use SV\UserMentionsImprovements\Str\ServiceUserGroupExtractorInterface;
use XF\Service\Message\Preparer as PreparerAlias;

/**
 * @extends \XF\Service\ProfilePostComment\Preparer
 */
class Preparer extends XFCP_Preparer implements ServiceUserGroupExtractorInterface
{
    use ServiceUserGroupExtractor;

    /**@var PreparerAlias|null */
    protected $svPreparer = null;

    /**
     * @param string $message
     * @param bool   $format
     * @return bool
     */
    public function setMessage($message, $format = true)
    {
        $ret = parent::setMessage($message, $format);

        $this->svCopyFields($this->svPreparer);

        return $ret;
    }

    /**
     * @param bool $format
     * @return PreparerAlias
     */
    protected function getMessagePreparer($format = true)
    {
        $preparer = parent::getMessagePreparer($format);
        $this->svPreparer = $preparer;

        return $preparer;
    }
}
