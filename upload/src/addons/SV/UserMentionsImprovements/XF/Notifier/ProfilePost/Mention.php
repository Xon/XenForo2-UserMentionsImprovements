<?php

namespace XF\Notifier\ProfilePost;

use XF\App;
use XF\Entity\ProfilePost;
use XF\Entity\User;
use XF\Notifier\AbstractNotifier;

class Mention extends AbstractNotifier
{
    /**
     * @var ProfilePost
     */
    protected $content;

    public function __construct(App $app, ProfilePost $content)
    {
        parent::__construct($app);

        $this->content = $content;
    }

    public function canNotify(User $user)
    {
        return ($user->user_id != $this->content->user_id);
    }

    public function sendAlert(User $user)
    {
        $content = $this->content;

        return $this->basicAlert($user, $content->user_id, $content->username, 'profile_post', $content->profile_post_id, 'mention');
    }

    public function sendEmail(User $user)
    {
        $params = [
            'profilePost' => $this->content,
            'receiver'    => $user
        ];

        $this->app->mailer()->newMail()
                  ->setToUser($user)
                  ->setTemplate('sv_user_quote_profile_post', $params)
                  ->queue();
    }
}