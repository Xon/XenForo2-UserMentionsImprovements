<?php

namespace SV\UserMentionsImprovements\XF\Service\Post;

class Notifier extends XFCP_Notifier
{
	protected function sendQuoteNotification(\XF\Entity\User $user)
	{
		$response = parent::sendQuoteNotification($user);

		if ($user->receivesQuoteEmails())
		{
			if (empty($this->usersEmailed[$user->user_id]) && $user->email && $user->user_state == 'valid')
			{
				$post = $this->post;

				$params = [
					'post' => $post,
					'thread' => $post->Thread,
					'forum' => $post->Thread->Forum,
					'receiver' => $user
				];

				$this->app->mailer()->newMail()
					->setToUser($user)
					->setTemplate('sv_usermentionsimprovements_quote', $params)
					->queue();

				$this->usersEmailed[$user->user_id] = true;
				$response = true;
			}
		}

		return $response;
	}

	protected function sendMentionNotification(\XF\Entity\User $user)
	{
		$response = parent::sendMentionNotification($user);

		if ($user->receivesMentionEmails())
		{
			if (empty($this->usersEmailed[$user->user_id]) && $user->email && $user->user_state == 'valid')
			{
				$post = $this->post;

				$params = [
					'post' => $post,
					'thread' => $post->Thread,
					'forum' => $post->Thread->Forum,
					'receiver' => $user
				];

				$this->app->mailer()->newMail()
					->setToUser($user)
					->setTemplate('sv_usermentionsimprovements_mention', $params)
					->queue();

				$this->usersEmailed[$user->user_id] = true;
				$response = true;
			}
		}

		return $response;
	}
}