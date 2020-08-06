<?php

namespace SV\UserMentionsImprovements\Str;

/**
 * This is basically a copy of the \XF\Str\MentionFormatter class, with changes for UserGroups instead.
 *
 * @package SV\UserMentionsImprovements\Str
 */
class UserGroupMentionFormatter
{
    protected $placeholders        = [];
    protected $mentionedUserGroups = [];

    /**
     * @param string $message
     * @return null|string
     */
    public function getMentionsBbCode($message)
    {
        // TODO: this regex needs to respect tags that disable parsing or tags that disable autolink
        $message = $this->setupPlaceholders(
            $message,
            '#\[(code|php|html|plain|media|url|img|user|quote|attach|usergroup)([= ][^\]]*)?](.*)\[/\\1]#siU'
        );

        $matches = $this->getPossibleMentionMatches($message);
        $usersByMatch = $this->getMentionMatchUserGroups($matches);

        $prefix = \XF::options()->userMentionKeepAt ? '@' : '';

        $message = $this->applyMentionUserGroupMatches(
            $message, $matches, $usersByMatch,
            function ($userGroup) use ($prefix) {


                return '[USERGROUP=' . $userGroup['user_group_id'] . ']' . $prefix . $userGroup['title'] . '[/USERGROUP]';
            }
        );

        $message = $this->restorePlaceholders($message);

        return $message;
    }

    /**
     * @param string $message
     * @return null|string
     */
    public function getMentionsStructuredText($message)
    {
        $message = $this->setupPlaceholders(
            $message,
            '#(?<=^|\s|[\](,]|--|@)@\[(\d+):(\'|"|&quot;|)(.*)\\2\]#iU'
        );

        $matches = $this->getPossibleMentionMatches($message);
        $usersByMatch = $this->getMentionMatchUserGroups($matches);

        $prefix = \XF::options()->userMentionKeepAt ? '@' : '';

        $message = $this->applyMentionUserGroupMatches(
            $message, $matches, $usersByMatch,
            function ($userGroup) use ($prefix) {
                if (strpos($userGroup['title'], ']') !== false)
                {
                    if (strpos($userGroup['title'], "'") !== false)
                    {
                        $title = '"' . $prefix . $userGroup['title'] . '"';
                    }
                    else
                    {
                        $title = "'" . $prefix . $userGroup['title'] . "'";
                    }
                }
                else
                {
                    $title = $prefix . $userGroup['title'];
                }

                return '@UG[' . $userGroup['user_group_id'] . ':' . $title . ']';
            }
        );

        $message = $this->restorePlaceholders($message);

        return $message;
    }

    public function getMentionedUserGroups()
    {
        return $this->mentionedUserGroups;
    }

    /**
     * @param string $message
     * @param string $regex
     * @return null|string
     */
    protected function setupPlaceholders($message, $regex)
    {
        $this->placeholders = [];

        return preg_replace_callback(
            $regex, function ($match) {
            $replace = "\x1A" . count($this->placeholders) . "\x1A";
            $this->placeholders[$replace] = $match[0];

            return $replace;
        }, $message
        );
    }

    protected function restorePlaceholders($message)
    {
        if ($this->placeholders)
        {
            $message = strtr($message, $this->placeholders);
            $this->placeholders = [];
        }

        return $message;
    }

    protected function getPossibleMentionMatches($message)
    {
        $min = 2;

        /** @noinspection RegExpRedundantEscape */
        if (!preg_match_all(
            '#(?<=^|\s|[\](,/\'"]|--)@(?!\[|\s)(([^\s@]|(?<![\s\](,-])@| ){' . $min . '}((?>[:,.!?](?=[^\s:,.!?[\]()])|' . $this->getTagEndPartialRegex(true) . '+?))*)#iu',
            $message, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER))
        {
            return [];
        }

        return $matches;
    }

    protected function getTagEndPartialRegex($negated)
    {
        return '[' . ($negated ? '^' : '') . ':;,.!?\s@\'"*/)\]\[-]';
    }

    /**
     * @param array $matches
     * @return array
     */
    protected function getMentionMatchUserGroups(array $matches)
    {
        $db = \XF::db();
        $matchKeys = array_keys($matches);
        $whereParts = [];
        $matchParts = [];
        $userGroupsByMatch = [];

        foreach ($matches AS $key => $match)
        {
            if (utf8_strlen($match[1][0]) > 50)
            {
                // longer than max usergroup title length
                continue;
            }

            $sql = 'usergroup.title LIKE ' . $db->quote($db->escapeLike($match[1][0], '?%'));
            $whereParts[] = $sql;
            $matchParts[] = 'IF(' . $sql . ', 1, 0) AS match_' . $key;
        }

        if (!$whereParts)
        {
            return [];
        }

        $sql = '';
        $visitor = \XF::visitor();
        $viewAllGroups = $visitor->hasPermission('general', 'sv_ViewPrivateGroups');
        // private groups are only view able by members and administrators.
        if (!$viewAllGroups)
        {
            $sql = ' AND ( usergroup.sv_private = 0 ';
            $groupMembership = array_filter(array_merge([$visitor->user_group_id], array_map('intval', $visitor->secondary_group_ids)));
            if ($groupMembership)
            {
                $sql .= ' or usergroup.user_group_id in ( ' . $db->quote($groupMembership) . ' )';
            }
            $sql .= ')';
        }

        $userGroupResults = $db->query(
            "
			SELECT usergroup.user_group_id, usergroup.title, usergroup.sv_private, usergroup.sv_mentionable,
				" . implode(', ', $matchParts) . "
			FROM xf_user_group AS usergroup
			WHERE sv_mentionable = 1 AND (" . implode(' OR ', $whereParts) . ") {$sql}
			ORDER BY LENGTH(usergroup.title) DESC
		"
        );

        while ($userGroup = $userGroupResults->fetch())
        {
            $userGroupInfo = [
                'user_group_id' => $userGroup['user_group_id'],
                'title'         => $userGroup['title'],
                'lower'         => utf8_strtolower($userGroup['title']),
            ];

            foreach ($matchKeys AS $key)
            {
                if (!empty($userGroup["match_$key"]))
                {
                    $userGroupsByMatch[$key][$userGroup['user_group_id']] = $userGroupInfo;
                }
            }
        }

        return $userGroupsByMatch;
    }

    /**
     * @param string   $message
     * @param array    $matches
     * @param array    $userGroupsByMatch
     * @param \Closure $tagReplacement
     * @return string
     */
    protected function applyMentionUserGroupMatches($message, array $matches, array $userGroupsByMatch, \Closure $tagReplacement)
    {
        $this->mentionedUserGroups = [];

        if (!$userGroupsByMatch)
        {
            return $message;
        }

        $newMessage = '';
        $lastOffset = 0;
        $testString = utf8_strtolower($message);
        $mentionedUserGroups = [];
        $endMatch = $this->getTagEndPartialRegex(false);

        foreach ($matches AS $key => $match)
        {
            if ($match[0][1] > $lastOffset)
            {
                $newMessage .= substr($message, $lastOffset, $match[0][1] - $lastOffset);
            }
            else if ($lastOffset > $match[0][1])
            {
                continue;
            }

            $lastOffset = $match[0][1] + strlen($match[0][0]);

            $haveMatch = false;
            if (!empty($userGroupsByMatch[$key]))
            {
                $testTitle = utf8_strtolower($match[1][0]);
                $testOffset = $match[1][1];

                foreach ($userGroupsByMatch[$key] AS $userGroupId => $userGroup)
                {
                    $titleLen = strlen($userGroup['lower']);
                    $nextTestOffsetStart = $testOffset + $titleLen;

                    if (
                        ($testTitle == $userGroup['lower'] || substr($testString, $testOffset, $titleLen) == $userGroup['lower'])
                        && (!isset($testString[$nextTestOffsetStart]) || preg_match('#' . $endMatch . '#i', $testString[$nextTestOffsetStart]))
                    )
                    {
                        $mentionedUserGroups[$userGroupId] = $userGroup;
                        $newMessage .= $tagReplacement($userGroup);
                        $haveMatch = true;
                        $lastOffset = $testOffset + strlen($userGroup['title']);
                        break;
                    }
                }
            }

            if (!$haveMatch)
            {
                $newMessage .= $match[0][0];
            }
        }

        $newMessage .= substr($message, $lastOffset);

        $this->mentionedUserGroups = $mentionedUserGroups;

        return $newMessage;
    }
}
