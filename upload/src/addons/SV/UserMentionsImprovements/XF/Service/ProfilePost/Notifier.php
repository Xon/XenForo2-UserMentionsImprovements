<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Service\ProfilePost;

use SV\UserMentionsImprovements\XF\Entity\User as ExtendedUserEntity;
use SV\UserMentionsImprovements\XF\Notifier\ProfilePost\Mention;
use XF\Entity\User as UserEntity;
use XF\Notifier\AbstractNotifier;
use function array_fill_keys;

/**
 * @extends \XF\Service\ProfilePost\Notifier
 */
class Notifier extends XFCP_Notifier
{
    /** @var AbstractNotifier */
    protected $svNotifier             = null;
    protected $notifyingMentionLookup = null;

    protected function sendNotification(UserEntity $user, $action)
    {
        $alerted = parent::sendNotification($user, $action);
        /** @var ExtendedUserEntity $user */
        if ($alerted && $user->receivesMentionEmails())
        {
            if ($this->notifyingMentionLookup === null)
            {
                $this->notifyingMentionLookup = array_fill_keys($this->notifyMentioned, true);
            }
            if ($action === 'insert' && isset($this->notifyingMentionLookup[$user->user_id]))
            {
                $action = 'mention';
            }
            if ($action === 'mention')
            {
                if (!$this->svNotifier)
                {
                    $class = \XF::extendClass(Mention::class);
                    $this->svNotifier = new $class($this->app, $this->profilePost);
                }

                $this->svNotifier->sendEmail($user);
            }
        }

        return $alerted;
    }
}