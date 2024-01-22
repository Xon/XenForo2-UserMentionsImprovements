<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\EmailStop;

use SV\UserMentionsImprovements\XF\Entity\UserOption as UserOptionEntity;
use XF\EmailStop\AbstractHandler;
use XF\Entity\User;

class Mention extends AbstractHandler
{
    public function getStopOneText(User $user, $contentId)
    {
        return null;
    }

    public function getStopAllText(User $user)
    {
        return \XF::phrase('sv_stop_notification_emails_for_all_mentions');
    }

    public function stopOne(User $user, $contentId)
    {
    }

    public function stopAll(User $user)
    {
        /** @var UserOptionEntity $option */
        $option = $user->Option;
        $option->sv_email_on_mention = false;
        $option->saveIfChanged();
    }
}