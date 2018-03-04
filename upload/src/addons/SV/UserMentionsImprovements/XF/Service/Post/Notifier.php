<?php

namespace SV\UserMentionsImprovements\XF\Service\Post;

use SV\UserMentionsImprovements\Globals;

class Notifier extends XFCP_Notifier
{
    protected $doCleanup = true;

    public function notifyAndEnqueue($timeLimit = null)
    {
        $this->doCleanup = false;
        try
        {
            //$this->notify($timeLimit);
            return $this->enqueueJobIfNeeded();
        }
        finally
        {
            $this->doCleanup = true;
            // need to cleanup the static, or it could contaminate the next job
            Globals::$userGroupMentionedIds = [];
        }
    }

    public function notify($timeLimit = null)
    {
        try
        {
            return parent::notify($timeLimit);
        }
        finally
        {
            // need to cleanup the static, or it could contaminate the next job
            if ($this->doCleanup)
            {
                Globals::$userGroupMentionedIds = [];
            }
        }
    }

    public function addNotification($type, $userId, $alert = true, $email = false)
    {
        $user = Globals::getUser($userId);

        $this->_addExtraAlertInfo($type, $user);

        parent::addNotification($type, $userId, $alert, $email);
    }

    public function addNotifications($type, array $userIds, $alert = true, $email = false)
    {
        parent::addNotifications($type, $userIds, $alert, $email);

        $users = Globals::getUsers($userIds);

        foreach ($users AS $user)
        {
            $this->_addExtraAlertInfo($type, $user);
        }
    }

    /**
     * @param \SV\UserMentionsImprovements\XF\Entity\User $user
     * @param string                                      $type
     */
    protected function _addExtraAlertInfo($type, $user)
    {
        switch ($type)
        {
            case 'quote':
                if (isset(Globals::$userGroupMentionedIds[$user->user_id]))
                {
                    $this->notifyData[$type][$user->user_id]['group'] = Globals::$userGroupMentionedIds[$user->user_id];
                }
                if ($user->receivesQuoteEmails())
                {
                    $this->notifyData[$type][$user->user_id]['email'] = true;
                }
                break;
            case 'mention':
                if (isset(Globals::$userGroupMentionedIds[$user->user_id]))
                {
                    $this->notifyData[$type][$user->user_id]['group'] = Globals::$userGroupMentionedIds[$user->user_id];
                }
                if ($user->receivesMentionEmails())
                {
                    $this->notifyData[$type][$user->user_id]['email'] = true;
                }
                break;
        }
    }

    protected function ensureDataLoaded()
    {
        parent::ensureDataLoaded();
        foreach (['mention', 'quote'] as $type)
        {
            if (isset($this->notifyData[$type]))
            {
                foreach ($this->notifyData[$type] as $userId => $value)
                {
                    if (isset($value['group']) && empty(Globals::$userGroupMentionedIds[$userId]))
                    {
                        Globals::$userGroupMentionedIds[$userId] = $value['group'];
                    }
                }
            }
        }
    }
}
