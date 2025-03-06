<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Notifier\Post;

use XF\Entity\User as UserEntity;

/**
 * @extends \XF\Notifier\Post\Mention
 */
class Mention extends XFCP_Mention
{
    public function canNotify(UserEntity $user)
    {
        $senderId = $this->post->user_id;
        if ($senderId && $user->isIgnoring($senderId))
        {
            return false;
        }

        return parent::canNotify($user);
    }

    public function sendEmail(UserEntity $user)
    {
        if (!$user->email || $user->user_state !== 'valid')
        {
            return;
        }

        $params = [
            'post'     => $this->post,
            'thread'   => $this->post->Thread,
            'forum'    => $this->post->Thread->Forum,
            'receiver' => $user,
        ];

        $this->app->mailer()->newMail()
                  ->setToUser($user)
                  ->setTemplate('sv_user_mention_post', $params)
                  ->queue();
    }
}
