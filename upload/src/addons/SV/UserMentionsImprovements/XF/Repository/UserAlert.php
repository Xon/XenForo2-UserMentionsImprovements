<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Repository;

use SV\UserMentionsImprovements\Globals;
use XF\Entity\User as UserEntity;

/**
 * @extends \XF\Repository\UserAlert
 */
class UserAlert extends XFCP_UserAlert
{
    public function alert(UserEntity $receiver, $senderId, $senderName, $contentType, $contentId, $action, array $extra = [], array $options = [])
    {
        $groups = Globals::$userGroupMentionedIds[$receiver->user_id] ?? null;
        if ($groups !== null && $action === 'mention')
        {
            $extra['sv_groups'] = $groups;
        }

        return parent::alert($receiver, $senderId, $senderName, $contentType, $contentId, $action, $extra, $options);
    }
}
