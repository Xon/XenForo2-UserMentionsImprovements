<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Notifier\ProfilePost;

use XF\App;
use XF\Entity\ProfilePost as ProfilePostEntity;
use XF\Entity\User as UserEntity;
use XF\Notifier\AbstractNotifier;

class Mention extends AbstractNotifier
{
    /**
     * @var ProfilePostEntity
     */
    protected $content;

    public function __construct(App $app, ProfilePostEntity $content)
    {
        parent::__construct($app);

        $this->content = $content;
    }

    public function canNotify(UserEntity $user)
    {
        $senderId = $this->content->user_id;
        if ($user->user_id === $senderId)
        {
            return false;
        }

        if ($senderId && $user->isIgnoring($senderId))
        {
            return false;
        }

        return true;
    }

    public function sendAlert(UserEntity $user)
    {
        $content = $this->content;

        return $this->basicAlert($user, $content->user_id, $content->username, 'profile_post', $content->profile_post_id, 'mention');
    }

    public function sendEmail(UserEntity $user)
    {
        if (!$user->email || $user->user_state !== 'valid')
        {
            return;
        }

        $params = [
            'profilePost' => $this->content,
            'receiver'    => $user,
        ];

        \XF::app()->mailer()->newMail()
                  ->setToUser($user)
                  ->setTemplate('sv_user_mention_profile_post', $params)
                  ->queue();
    }
}