<?php

namespace SV\UserMentionsImprovements\XF\Service\Post;

class Notifier extends XFCP_Notifier
{
	public function addNotification($type, $userId, $alert = true, $email = false)
	{
		$user = $this->app->find('XF:User', $userId);

		switch ($type)
		{
			case 'quote':
				if ($user->receivesQuoteEmails())
				{
					$this->notifyData[$type][$user->user_id]['email'] = true;
				}
				break;
			case 'mention':
				if ($user->receivesMentionEmails())
				{
					$this->notifyData[$type][$user->user_id]['email'] = true;
				}
				break;
		}

		parent::addNotification($type, $userId, $alert, $email);
	}

	public function addNotifications($type, array $userIds, $alert = true, $email = false)
	{
		parent::addNotifications($type, $userIds, $alert, $email);

		$users = $this->app->em()->findByIds('XF:User', $userIds);

		foreach ($users AS $user)
		{
			/** @var \SV\UserMentionsImprovements\XF\Entity\User $user */

			if ($user->receivesQuoteEmails())
			{
				switch ($type)
				{
					case 'quote':
						if ($user->receivesQuoteEmails())
						{
							$this->notifyData[$type][$user->user_id]['email'] = true;
						}
						break;
					case 'mention':
						if ($user->receivesMentionEmails())
						{
							$this->notifyData[$type][$user->user_id]['email'] = true;
						}
						break;
				}
			}
		}
	}
}