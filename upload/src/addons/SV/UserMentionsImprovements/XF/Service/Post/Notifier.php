<?php

namespace SV\UserMentionsImprovements\XF\Service\Post;

use SV\UserMentionsImprovements\Globals;
use SV\UserMentionsImprovements\XF\Entity\User;

class Notifier extends XFCP_Notifier
{
    protected $doCleanup = true;

    /**
     * @param int[] $userIds
     * @return User[]
     */
    public function getUsers(array $userIds = null)
    {
        if (!$userIds)
        {
            return [];
        }
        $em = \XF::em();
        /** @var User[] $users */
        $users = [];
        $userIdsToFetch = [];
        foreach($userIds as $userId)
        {
            $user = $em->findCached('XF:User', $userId);
            if ($user)
            {
                $users[$userId] = $user;
            }
            else
            {
                $userIdsToFetch[] = $userId;
            }
        }

        if ($userIdsToFetch)
        {
            $users = \array_merge($users, $em->findByIds('XF:User', $userIdsToFetch)->toArray());
        }

        return $users;
    }

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
        parent::addNotification($type, $userId, $alert, $email);

        $this->_addExtraAlertInfo($type, [$userId]);
    }

    public function addNotifications($type, array $userIds, $alert = true, $email = false)
    {
        parent::addNotifications($type, $userIds, $alert, $email);

        $this->_addExtraAlertInfo($type, $userIds);
    }

    /**
     * @param string $type
     * @param int[]  $userIds
     */
    protected function _addExtraAlertInfo($type, array $userIds)
    {
        switch ($type)
        {
            case 'quote':
                if (\XF::options()->sv_limit_quote_emails)
                {
                    $db = \XF::db();
                    $ids = [];
                    $threadId = $this->post->thread_id;
                    foreach ($userIds as $id)
                    {
                        $id = intval($id);
                        if ($id)
                        {
                            $ids[] = "SELECT $id AS id";
                        }
                    }
                    /** @var int[] $userIds */
                    /** @noinspection SqlResolve */
                    $userIds = $db->fetchAllColumn("
                        SELECT DISTINCT a.id
                        FROM ( " . join(' union ', $ids) . " ) a
                        LEFT JOIN xf_thread_user_post ON (xf_thread_user_post.thread_id = {$threadId} AND xf_thread_user_post.user_id = a.id)
                        WHERE xf_thread_user_post.user_id IS null
                    ");
                }
                $users = $this->getUsers($userIds);
                foreach($userIds as $userId)
                {
                    if (isset(Globals::$userGroupMentionedIds[$userId]))
                    {
                        $this->notifyData[$type][$userId]['group'] = Globals::$userGroupMentionedIds[$userId];
                    }
                    $user = isset($users[$userId]) ? $users[$userId] : null;
                    if ($user && $user->receivesQuoteEmails())
                    {
                        $this->notifyData[$type][$userId]['email'] = true;
                    }
                }
                break;
            case 'mention':
                $users = $this->getUsers($userIds);
                foreach($userIds as $userId)
                {
                    if (isset(Globals::$userGroupMentionedIds[$userId]))
                    {
                        $this->notifyData[$type][$userId]['group'] = Globals::$userGroupMentionedIds[$userId];
                    }
                    $user = isset($users[$userId]) ? $users[$userId] : null;
                    if ($user && $user->receivesMentionEmails())
                    {
                        $this->notifyData[$type][$userId]['email'] = true;
                    }
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
