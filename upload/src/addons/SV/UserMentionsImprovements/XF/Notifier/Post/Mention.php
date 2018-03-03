<?php

namespace SV\UserMentionsImprovements\XF\Notifier\Post;

use XF\Entity\User;

class Mention extends XFCP_Mention
{
    public function sendEmail(User $user)
    {
        $params = [
            'post'  => $this->post,
            'thread'   => $this->post->Thread,
            'forum'    => $this->post->Thread->Forum,
            'receiver' => $user
        ];

        $this->app->mailer()->newMail()
                  ->setToUser($user)
                  ->setTemplate('sv_user_mention_post', $params)
                  ->queue();
    }
}
