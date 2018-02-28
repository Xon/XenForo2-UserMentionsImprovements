<?php

namespace XF\Notifier\Report;

use XF\App;
use XF\Entity\ReportComment;
use XF\Entity\User;
use XF\Notifier\AbstractNotifier;

class Mention extends AbstractNotifier
{
    /**
     * @var ReportComment
     */
    protected $content;

    public function __construct(App $app, ReportComment $content)
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

        return $this->basicAlert($user, $content->user_id, $content->username, 'report_comment', $content->report_comment_id, 'mention');
    }

    public function sendEmail(User $user)
    {
        $params = [
            'reportComment' => $this->content,
            'receiver'    => $user
        ];

        $this->app->mailer()->newMail()
                  ->setToUser($user)
                  ->setTemplate('sv_user_quote_report_comment', $params)
                  ->queue();
    }
}