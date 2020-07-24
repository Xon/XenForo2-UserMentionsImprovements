<?php

namespace SV\UserMentionsImprovements\XF\Repository\XF2;

use SV\UserMentionsImprovements\Globals;
use SV\UserMentionsImprovements\XF\Repository\XFCP_UserAlert;
use XF\Entity\User;

class UserAlert extends XFCP_UserAlert
{
    /**
     * XF2.0-XF2.1 support
     *
     * @noinspection PhpSignatureMismatchDuringInheritanceInspection
     * @param User  $receiver
     * @param       $senderId
     * @param       $senderName
     * @param       $contentType
     * @param       $contentId
     * @param       $action
     * @param array $extra
     * @return bool
     */
    public function alert(User $receiver, $senderId, $senderName, $contentType, $contentId, $action, array $extra = [])
    {
        if (isset(Globals::$userGroupMentionedIds[$receiver->user_id]) && $action === 'mention')
        {
            $extra['sv_group'] = Globals::$userGroupMentionedIds[$receiver->user_id];
        }

        return parent::alert($receiver, $senderId, $senderName, $contentType, $contentId, $action, $extra);
    }
}
