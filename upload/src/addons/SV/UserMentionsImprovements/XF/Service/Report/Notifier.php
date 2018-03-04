<?php

namespace SV\UserMentionsImprovements\XF\Service\Report;

use XF\Entity\User;
use XF\Notifier\AbstractNotifier;

/**
 * Extends \XF\Service\Report\Notifier
 */
class Notifier extends XFCP_Notifier
{
    /** @var AbstractNotifier */
    protected $svNotifier = null;

    protected function sendMentionNotification(User $user)
    {
        $alerted = parent::sendMentionNotification($user);
        if ($alerted)
        {
            if (!$this->svNotifier)
            {
                $class = \XF::extendClass('SV\UserMentionsImprovements\XF\Notifier\Report\Mention');
                $this->svNotifier = new $class($this->app, $this->comment);
            }

            $this->svNotifier->sendEmail($user);
        }

        return $alerted;
    }
}