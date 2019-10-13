<?php

namespace SV\UserMentionsImprovements\XF\Notifier\Post;

use XF\Entity\User;

class Mention extends XFCP_Mention
{
    public function canNotify(User $user)
    {
        $senderId = $this->post->user_id;
        if ($senderId && $user->isIgnoring($senderId))
        {
            return false;
        }

        return parent::canNotify($user);
    }

    public function sendEmail(User $user)
    {
        if (!$user->email || $user->user_state != 'valid')
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
