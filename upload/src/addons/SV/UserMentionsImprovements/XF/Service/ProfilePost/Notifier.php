<?php

namespace SV\UserMentionsImprovements\XF\Service\ProfilePost;

use XF\Entity\User;
use XF\Notifier\AbstractNotifier;

/**
 * Extends \XF\Service\ProfilePost\Notifier
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
            if ($action === 'insert' && isset($this->notifyingMentionLookup[$user->user_id]))
            {
                $action = 'mention';
            }
            if ($action === 'mention')
            {
                if (!$this->svNotifier)
                {
                    $class = \XF::extendClass('SV\UserMentionsImprovements\XF\Notifier\ProfilePost\Mention');
                    $this->svNotifier = new $class($this->app, $this->profilePost);
                }

                $this->svNotifier->sendEmail($user);
            }
        }

        return $alerted;
    }
}