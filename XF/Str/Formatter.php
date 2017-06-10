<?php

namespace SV\UserMentionsImprovements\XF\Str;

class Formatter extends XFCP_Formatter
{
	/**
	 * @return \SV\UserMentionsImprovements\Str\UserGroupMentionFormatter
	 */
	public function getUserGroupMentionFormatter()
	{
		$class = \XF::extendClass('SV\UserMentionsImprovements\Str\UserGroupMentionFormatter');

		return new $class();
	}
}