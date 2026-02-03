<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Service\ProfilePostComment;

use SV\UserMentionsImprovements\XF\Entity\User as ExtendedUserEntity;
use SV\UserMentionsImprovements\XF\Notifier\ProfilePostComment\Mention;
use XF\Entity\User as UserEntity;
use XF\Notifier\AbstractNotifier;
use function array_fill_keys;

/**
 * @extends \XF\Service\ProfilePostComment\Notifier
 */
class Notifier extends XFCP_Notifier
{
    /** @var AbstractNotifier */
    protected $svNotifier = null;
    /** @var array|null */
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
            if ($action === 'your_profile' && isset($this->notifyingMentionLookup[$user->user_id]))
            {
                $action = 'mention';
            }
            if ($action === 'mention')
            {
                if (!$this->svNotifier)
                {
                    $class = \XF::extendClass(Mention::class);
                    $this->svNotifier = new $class($this->app, $this->comment);
                }

                $this->svNotifier->sendEmail($user);
            }
        }

        return $alerted;
    }
}