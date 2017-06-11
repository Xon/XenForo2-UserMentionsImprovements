<?php

namespace SV\UserMentionsImprovements\XF\BbCode\Renderer;

class Html extends XFCP_Html
{
	public function addDefaultTags()
	{
		parent::addDefaultTags();

		$this->addTag('usergroup', [
			'callback' => 'renderTagUserGroup'
		]);
	}

	public function renderTagUserGroup(array $children, $option, array $tag, array $options)
	{
		$content = $this->renderSubTree($children, $options);
		if ($content === '')
		{
			return '';
		}

		$userGroupId = intval($option);
		if ($userGroupId <= 0)
		{
			return $content;
		}

		$link = \XF::app()->router('public')->buildLink('full:members/usergroup', ['user_group_id' => $userGroupId]);

		return $this->wrapHtml(
			'<a href="' . htmlspecialchars($link) . '" class="usergroup">',
			$content,
			'</a>'
		);
	}
}