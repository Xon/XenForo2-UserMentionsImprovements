<?php

namespace SV\UserMentionsImprovements\XF\Service\ProfilePostComment;

use XF\Entity\User;
use XF\Notifier\AbstractNotifier;

/**
 * Extends \XF\Service\ProfilePostComment\Notifier
 */
class Notifier extends XFCP_Notifier
{
    /** @var AbstractNotifier */
    protected $svNotifier             = null;
    protected $notifyingMentionLookup = null;

    protected function sendNotification(User $user, $action)
    {
        $alerted = parent::sendNotification($user, $action);
        /** @var \SV\UserMentionsImprovements\XF\Entity\User $user */
        if ($alerted && $user->receivesMentionEmails())
        {
            if ($this->notifyingMentionLookup === null)
            {
                $this->notifyingMentionLookup = \array_fill_keys($this->notifyMentioned, true);
            }
            if ($action === 'your_profile' && isset($this->notifyingMentionLookup[$user->user_id]))
            {
                $action = 'mention';
            }
            if ($action === 'mention')
            {
                if (!$this->svNotifier)
                {
                    $class = \XF::extendClass('SV\UserMentionsImprovements\XF\Notifier\ProfilePostComment\Mention');
                    $this->svNotifier = new $class($this->app, $this->comment);
                }

                $this->svNotifier->sendEmail($user);
            }
        }

        return $alerted;
    }
}