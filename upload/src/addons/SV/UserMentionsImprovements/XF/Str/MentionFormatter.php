<?php

namespace SV\UserMentionsImprovements\XF\Str;

use SV\UserMentionsImprovements\XF\BbCode\ProcessorAction\MentionUsers;
use function count;
use function is_callable;
use function preg_replace_callback;

/**
 * @extends \XF\Str\MentionFormatter
 */
class MentionFormatter extends XFCP_MentionFormatter
{
    /** @var MentionUsers */
    public $svMentionUserGroup = null;
    /** string[] */
    protected $svPlaceholders;

    protected function svSetupPlaceholders(string $message, string $regex): string
    {
        $this->svPlaceholders = [];

        return preg_replace_callback(
                   $regex, function ($match) {
                   $replace = "\x1B" . count($this->svPlaceholders) . "\x1B";
                   $this->svPlaceholders[$replace] = $match[0];

                   return $replace;
               }, $message
               ) ?? '';
    }

    protected function svRestorePlaceholders(string $message): string
    {
        if ($this->svPlaceholders)
        {
            $message = strtr($message, $this->svPlaceholders);
            $this->svPlaceholders = [];
        }

        return $message;
    }

    /**
     * @param string $string
     * @param array  $mentionedUserGroups
     * @return string
     */
    protected function extractMentionedUserGroups(string $string, array &$mentionedUserGroups): string
    {
        if (!$this->svMentionUserGroup)
        {
            return $string;
        }

        $formatter = $this->svMentionUserGroup->getFormatter();
        if (!is_callable([$formatter, 'getUserGroupMentionFormatter']))
        {
            \XF::logError('Add-on conflict detected, XF\Str\Formatter is not extended as expected', true);

            return $string;
        }
        $userGroupMentions = $formatter->getUserGroupMentionFormatter();

        $string = $userGroupMentions->getMentionsBbCode($string);
        $mentionedUserGroups = $userGroupMentions->getMentionedUserGroups();

        return $string;
    }

    /**
     * @param string $message
     * @return string
     * @noinspection PhpUnnecessaryLocalVariableInspection
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function getMentionsBbCode($message)
    {
        $mentionedUserGroups = [];
        $message = $this->extractMentionedUserGroups($message, $mentionedUserGroups);

        if ($mentionedUserGroups)
        {
            $this->svMentionUserGroup->setMentionedUsers($mentionedUserGroups);
            /** @noinspection RegExpRedundantEscape */
            $message = $this->svSetupPlaceholders($message,
                '#\[(usergroup)(=[^\]]*)?](.*)\[/\\1]#siU'
            );
        }

        $message = parent::getMentionsBbCode($message);

        $message = $this->svRestorePlaceholders($message);

        return $message;
    }
}