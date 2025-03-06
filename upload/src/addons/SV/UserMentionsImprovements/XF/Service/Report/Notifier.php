<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Service\Report;

use SV\UserMentionsImprovements\XF\Entity\User as ExtendedUserEntity;
use SV\UserMentionsImprovements\XF\Notifier\Report\Mention;
use XF\App;
use XF\Entity\Report as ReportEntity;
use XF\Entity\ReportComment as ReportCommentEntity;
use XF\Entity\User as UserEntity;
use XF\Notifier\AbstractNotifier;

/**
 * @extends \XF\Service\Report\Notifier
 */
class Notifier extends XFCP_Notifier
{
    protected $userEmailed = [];
    /** @var AbstractNotifier */
    protected $svReportMentionNotifier = null;

    public function __construct(App $app, ReportEntity $report, ReportCommentEntity $comment)
    {
        parent::__construct($app, $report, $comment);

        $class = \XF::extendClass(Mention::class);
        $this->svReportMentionNotifier = new $class($this->app, $this->comment);
    }

    protected function sendMentionNotification(UserEntity $user)
    {
        $alerted = parent::sendMentionNotification($user);
        $userId = $user->user_id;

        /** @var ExtendedUserEntity $user */
        if (empty($this->userEmailed[$userId]) &&
            $this->svReportMentionNotifier->canNotify($user) &&
            $user->receivesMentionEmails())
        {
            $this->svReportMentionNotifier->sendEmail($user);

            $this->userEmailed[$userId] = true;
        }

        return $alerted;
    }
}