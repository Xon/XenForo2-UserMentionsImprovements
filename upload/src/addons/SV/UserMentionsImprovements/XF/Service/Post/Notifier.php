<?php

namespace SV\UserMentionsImprovements\XF\Service\Post;

use SV\StandardLib\Helper;
use SV\UserMentionsImprovements\Globals;
use SV\UserMentionsImprovements\XF\Entity\User as ExtendedUserEntity;
use XF\Entity\User as UserEntity;

/**
 * @extends \XF\Service\Post\Notifier
 */
class Notifier extends XFCP_Notifier
{
    protected $doCleanup = true;

    /**
     * @param int[] $userIds
     * @return ExtendedUserEntity[]
     */
    public function getSvUsers(array $userIds): array
    {
        if (!$userIds)
        {
            return [];
        }

        /** @var ExtendedUserEntity[] $users */
        $users = [];
        $userIdsToFetch = [];
        foreach ($userIds as $userId)
        {
            $user = Helper::findCached(UserEntity::class, $userId);
            if ($user !== null)
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
            $users += Helper::findByIds(UserEntity::class, $userIdsToFetch)->toArray();
        }

        return $users;
    }

    public function svShouldFullyDefer(): bool
    {
        $this->ensureDataLoaded();

        if (isset($this->notifyData['threadStarter']) && \is_array($this->notifyData['threadStarter']))
        {
            // most alerts should be just the thread starter
            return \count($this->notifyData['threadStarter']) > 4 * self::USERS_PER_CYCLE;
        }
        else if (isset($this->notifyData['followedUsers']) && \is_array($this->notifyData['followedUsers']))
        {
            // most alerts should be to followers
            return \count($this->notifyData['followedUsers']) > 4 * self::USERS_PER_CYCLE;
        }

        // most users shouldn't be alerted
        $limit = 6 * self::USERS_PER_CYCLE;
        foreach ($this->notifyData as $data)
        {
            $userCount = \is_array($data) ? \count($data) : 0;
            if ($userCount > $limit)
            {
                return true;
            }
        }

        return false;
    }

    /** @noinspection PhpMissingReturnTypeInspection */
    public function notifyAndEnqueue($timeLimit = null)
    {
        $this->doCleanup = false;
        try
        {
            if (!$this->svShouldFullyDefer())
            {
                $this->notify($timeLimit === 3 ? 0.5 : $timeLimit);
            }
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
            parent::notify($timeLimit);
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

        $this->svAddExtraAlertInfo($type, [$userId]);
    }

    public function addNotifications($type, array $userIds, $alert = true, $email = false)
    {
        parent::addNotifications($type, $userIds, $alert, $email);

        $this->svAddExtraAlertInfo($type, $userIds);
    }

    /**
     * @param string $type
     * @param int[]|string[]  $userIds
     */
    protected function svAddExtraAlertInfo(string $type, array $userIds)
    {
        switch ($type)
        {
            case 'quote':
                if ($userIds && (\XF::options()->sv_limit_quote_emails ?? false))
                {
                    $db = \XF::db();
                    $ids = [];
                    $threadId = $this->post->thread_id;
                    foreach ($userIds as $id)
                    {
                        $id = \intval($id);
                        if ($id)
                        {
                            $ids[] = "SELECT $id AS id";
                        }
                    }
                    /** @var int[] $userIds */
                    /** @noinspection SqlResolve */
                    /** @noinspection SqlConstantExpression */
                    $userIds = $ids ? $db->fetchAllColumn('
                        SELECT DISTINCT a.id
                        FROM ( ' . join(' union ', $ids) . ' ) a
                        LEFT JOIN xf_thread_user_post ON (xf_thread_user_post.thread_id = ? AND xf_thread_user_post.user_id = a.id)
                        WHERE xf_thread_user_post.user_id IS null
                    ', [$threadId]) : [];
                }
                $users = $this->getSvUsers($userIds);
                foreach ($userIds as $userId)
                {
                    $groups = Globals::$userGroupMentionedIds[$userId] ?? null;
                    if ($groups !== null)
                    {
                        $this->notifyData[$type][$userId]['groups'] = $groups;
                    }
                    $user = $users[$userId] ?? null;
                    if ($user && $user->receivesQuoteEmails())
                    {
                        $this->notifyData[$type][$userId]['email'] = true;
                    }
                }
                break;
            case 'mention':
                $users = $this->getSvUsers($userIds);
                foreach ($userIds as $userId)
                {
                    $groups = Globals::$userGroupMentionedIds[$userId] ?? null;
                    if ($groups !== null)
                    {
                        $this->notifyData[$type][$userId]['groups'] = $groups;
                    }
                    $user = $users[$userId] ?? null;
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
            $notifyData = $this->notifyData[$type] ?? null;
            if (\is_array($notifyData))
            {
                foreach ($this->notifyData[$type] as $userId => $value)
                {
                    if (empty(Globals::$userGroupMentionedIds[$userId]))
                    {
                        $groupsData = $value['groups'] ?? null;
                        if ($groupsData !== null)
                        {
                            Globals::$userGroupMentionedIds[$userId] = $groupsData;
                        }
                        else
                        {
                            $groupData = $value['group'] ?? null;
                            if ($groupData !== null)
                            {
                                Globals::$userGroupMentionedIds[$userId] = [$groupsData];
                            }
                        }
                    }
                }
            }
        }
    }
}
