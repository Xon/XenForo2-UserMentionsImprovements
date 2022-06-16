<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Repository;

use SV\UserMentionsImprovements\Globals;
use XF\Entity\User;

class UserAlert extends XFCP_UserAlert
{
    public function alert(User $receiver, $senderId, $senderName, $contentType, $contentId, $action, array $extra = [], array $options = [])
    {
        $groups = Globals::$userGroupMentionedIds[$receiver->user_id] ?? null;
        if ($groups !== null && $action === 'mention')
        {
            $extra['sv_groups'] = $groups;
        }

        return parent::alert($receiver, $senderId, $senderName, $contentType, $contentId, $action, $extra, $options);
    }
}
