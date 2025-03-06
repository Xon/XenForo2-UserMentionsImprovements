<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Notifier\Report;

use XF\App;
use XF\Entity\ReportComment as ReportCommentEntity;
use XF\Entity\User as UserEntity;
use XF\Notifier\AbstractNotifier;

class Mention extends AbstractNotifier
{
    /**
     * @var ReportCommentEntity
     */
    protected $content;

    public function __construct(App $app, ReportCommentEntity $content)
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

        return $this->basicAlert($user, $content->user_id, $content->username, 'report_comment', $content->report_comment_id, 'mention');
    }

    public function sendEmail(UserEntity $user)
    {
        if (!$user->email || $user->user_state !== 'valid')
        {
            return;
        }

        $params = [
            'reportComment' => $this->content,
            'receiver'      => $user,
        ];

        $this->app->mailer()->newMail()
                  ->setToUser($user)
                  ->setTemplate('sv_user_mention_report_comment', $params)
                  ->queue();
    }
}