<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Notifier\ProfilePostComment;

use XF\App;
use XF\Entity\ProfilePostComment as ProfilePostCommentEntity;
use XF\Entity\User as UserEntity;
use XF\Notifier\AbstractNotifier;

class Mention extends AbstractNotifier
{
    /**
     * @var ProfilePostCommentEntity
     */
    protected $content;

    public function __construct(App $app, ProfilePostCommentEntity $content)
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

        return $this->basicAlert($user, $content->user_id, $content->username, 'profile_post_comment', $content->profile_post_comment_id, 'mention');
    }

    public function sendEmail(UserEntity $user)
    {
        if (!$user->email || $user->user_state !== 'valid')
        {
            return;
        }

        $params = [
            'profilePostComment' => $this->content,
            'receiver'           => $user,
        ];

        \XF::app()->mailer()->newMail()
                  ->setToUser($user)
                  ->setTemplate('sv_user_mention_profile_post_comment', $params)
                  ->queue();
    }
}