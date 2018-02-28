<?php

namespace SV\UserMentionsImprovements\XF\Notifier\Post;

use XF\Entity\User;

class Quote extends XFCP_Quote
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
                  ->setTemplate('sv_user_quote', $params)
                  ->queue();
    }
}
