<?php

namespace SV\UserMentionsImprovements\XF\Notifier\Post;

class Quote extends XFCP_Quote
{
    public function sendEmail(\XF\Entity\User $user)
    {
        $params = [
            'post'     => $this->post,
            'thread'   => $this->post->Thread,
            'forum'    => $this->post->Thread->Forum,
            'receiver' => $user
        ];

        $this->app->mailer()->newMail()
                  ->setToUser($user)
                  ->setTemplate('sv_usermentionsimprovements_quote', $params)
                  ->queue();
    }
}
