<?php

namespace SV\UserMentionsImprovements\XF\Notifier\ProfilePostComment;

use XF\App;
use XF\Entity\ProfilePostComment;
use XF\Entity\User;
use XF\Notifier\AbstractNotifier;

class Mention extends AbstractNotifier
{
    /**
     * @var ProfilePostComment
     */
    protected $content;

    public function __construct(App $app, ProfilePostComment $content)
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

        return $this->basicAlert($user, $content->user_id, $content->username, 'profile_post_comment', $content->profile_post_comment_id, 'mention');
    }

    public function sendEmail(User $user)
    {
        $params = [
            'profilePostComment' => $this->content,
            'receiver'           => $user
        ];

        $this->app->mailer()->newMail()
                  ->setToUser($user)
                  ->setTemplate('sv_user_mention_profile_post_comment', $params)
                  ->queue();
    }
}