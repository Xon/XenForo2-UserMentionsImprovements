<?php

namespace SV\UserMentionsImprovements\XF\Repository;

use SV\UserMentionsImprovements\Globals;
use XF\Entity\User;

class UserAlert extends XFCP_UserAlert
{
    public function alert(User $receiver, $senderId, $senderName, $contentType, $contentId, $action, array $extra = [])
    {
        if (isset(Globals::$userGroupMentionedIds[$receiver->user_id]) && $action === 'mention')
        {
            $extra['fromUserGroupMention'] = Globals::$userGroupMentionedIds[$receiver->user_id];
        }

        return parent::alert($receiver, $senderId, $senderName, $contentType, $contentId, $action, $extra);
    }
}
