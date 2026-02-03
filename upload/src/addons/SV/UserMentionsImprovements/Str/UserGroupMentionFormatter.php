<?php
/**
 * @noinspection RegExpRedundantEscape
 */

namespace SV\UserMentionsImprovements\Str;

use function array_filter;
use function array_keys;
use function array_map;
use function array_merge;
use function count;
use function implode;
use function mb_strlen;
use function mb_strtolower;
use function preg_match;
use function preg_match_all;
use function preg_quote;
use function preg_replace_callback;
use function strlen;
use function strpos;
use function substr;

/**
 * This is basically a copy of the \XF\Str\MentionFormatter class, with changes for UserGroups instead.
 *
 * @package SV\UserMentionsImprovements\Str
 */
class UserGroupMentionFormatter
{
    public const STRUCTURED_MENTION_REGEX = '#(?<=^|\s|[\](,/\'"]|--|@)@\[(\d+):(\'|"|&quot;|)(.*)\\2\]#iU';

    protected $placeholders        = [];
    protected $mentionedUserGroups = [];

    /**
     * @param string $message
     * @return string
     * @noinspection PhpUnnecessaryLocalVariableInspection
     */
    public function getMentionsBbCode(string $message): string
    {
        $disabledTags = array_map(
            function ($v) { return preg_quote($v, '#'); },
            $this->getMentionDisabledBbCodeTags()
        );
        $message = $this->setupPlaceholders($message,
            '#\[(' . implode('|', $disabledTags) . ')([= ][^\]]*)?](.*)\[/\\1]#siU'
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

    protected function getMentionDisabledBbCodeTags(): array
    {
        $bbCodeRules = \XF::app()->bbCode()->rules('mentions');

        $disabledTags = [];
        foreach ($bbCodeRules->getTags() as $tagName => $tag)
        {
            if (!empty($tag['stopAutoLink']) || !empty($tag['plain']))
            {
                $disabledTags[] = $tagName;
            }
        }

        // technically mentions can be parsed in quotes, but it's likely they already have
        // and this isn't necessarily text that we want to attribute to the poster
        $disabledTags[] = 'quote';

        return $disabledTags;
    }

    /**
     * @param string $message
     * @return string
     * @noinspection PhpUnnecessaryLocalVariableInspection
     */
    public function getMentionsStructuredText(string $message): string
    {
        $message = $this->setupPlaceholders($message, self::STRUCTURED_MENTION_REGEX);

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

    public function getMentionedUserGroups(): array
    {
        return $this->mentionedUserGroups;
    }

    /**
     * @param string $message
     * @param string $regex
     * @return string
     */
    protected function setupPlaceholders(string $message, string $regex): string
    {
        $this->placeholders = [];

        return preg_replace_callback($regex, function ($match) {
            $replace = "\x1A" . count($this->placeholders) . "\x1A";
            $this->placeholders[$replace] = $match[0];

            return $replace;
        }, $message) ?? '';
    }

    protected function restorePlaceholders(string $message): string
    {
        if ($this->placeholders)
        {
            $message = strtr($message, $this->placeholders);
            $this->placeholders = [];
        }

        return $message;
    }

    protected function getPossibleMentionMatches(string $message): array
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

    protected function getTagEndPartialRegex(string $negated): string
    {
        return '[' . ($negated ? '^' : '') . ':;,.!?\s@\'"*/)\]\[-]';
    }

    /**
     * @param array $matches
     * @return array
     * @noinspection PhpDocMissingThrowsInspection
     */
    protected function getMentionMatchUserGroups(array $matches): array
    {
        $db = \XF::db();
        $matchKeys = array_keys($matches);
        $whereParts = [];
        $matchParts = [];
        $userGroupsByMatch = [];

        foreach ($matches as $key => $match)
        {
            if (mb_strlen($match[1][0]) > 50)
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
            $groupMembership = array_filter(array_merge([$visitor->user_group_id], array_map('\intval', $visitor->secondary_group_ids)));
            if ($groupMembership)
            {
                $sql .= ' or usergroup.user_group_id in ( ' . $db->quote($groupMembership) . ' )';
            }
            $sql .= ')';
        }

        $userGroupResults = $db->query('
			SELECT usergroup.user_group_id, usergroup.title, usergroup.sv_private, usergroup.sv_mentionable,
				' . implode(', ', $matchParts) . '
			FROM xf_user_group AS usergroup
			WHERE sv_mentionable = 1 AND (' . implode(' OR ', $whereParts) . ') ' . $sql . '
			ORDER BY LENGTH(usergroup.title) DESC
		');

        while ($userGroup = $userGroupResults->fetch())
        {
            $userGroupInfo = [
                'user_group_id' => $userGroup['user_group_id'],
                'title'         => $userGroup['title'],
                'lower'         => mb_strtolower($userGroup['title']),
            ];

            foreach ($matchKeys as $key)
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
    protected function applyMentionUserGroupMatches(string $message, array $matches, array $userGroupsByMatch, \Closure $tagReplacement): string
    {
        $this->mentionedUserGroups = [];

        if (!$userGroupsByMatch)
        {
            return $message;
        }

        $newMessage = '';
        $lastOffset = 0;
        $mentionedUserGroups = [];
        $endMatch = $this->getTagEndPartialRegex(false);

        foreach ($matches as $key => $match)
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
                $testName = mb_strtolower($match[1][0]);
                $testOffset = $match[1][1];

                foreach ($userGroupsByMatch[$key] as $userGroupId => $userGroup)
                {
                    // It's possible for the byte length to change between the lower and standard versions
                    // due to conversions like İ -> i (2 byte to 1). Therefore, we try to check whether either
                    // length matches the name.
                    $lowerLen = strlen($userGroup['lower']);
                    $originalLen = strlen($userGroup['title']);

                    if ($testName === $userGroup['lower'])
                    {
                        $nameLen = $lowerLen;
                    }
                    else if (mb_strtolower(substr($message, $testOffset, $lowerLen)) === $userGroup['lower'])
                    {
                        $nameLen = $lowerLen;
                    }
                    else if (
                        $lowerLen !== $originalLen
                        && mb_strtolower(substr($message, $testOffset, $originalLen)) === $userGroup['lower']
                    )
                    {
                        $nameLen = $originalLen;
                    }
                    else
                    {
                        $nameLen = null;
                    }

                    $nextTestOffsetStart = $testOffset + ($nameLen ?: 0);

                    if (
                        $nameLen
                        && (
                            !isset($message[$nextTestOffsetStart])
                            || preg_match('#' . $endMatch . '#i', $message[$nextTestOffsetStart])
                        )
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
