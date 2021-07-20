<?php

namespace SV\UserMentionsImprovements\XF\Repository\XF22;

use SV\UserMentionsImprovements\Globals;
use SV\UserMentionsImprovements\XF\Repository\XFCP_UserAlert;
use XF\Entity\User;

class UserAlert extends XFCP_UserAlert
{
    /*
     * XF2.2+ support
     */
    public function alert(User $receiver, $senderId, $senderName, $contentType, $contentId, $action, array $extra = [], array $options = [])
    {
        $group = Globals::$userGroupMentionedIds[$receiver->user_id] ?? null;
        if ($group !== null && $action === 'mention')
        {
            $extra['sv_group'] = $group;
        }

        return parent::alert($receiver, $senderId, $senderName, $contentType, $contentId, $action, $extra, $options);
    }
}
