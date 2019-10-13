<?php

namespace SV\UserMentionsImprovements\XF\BbCode\ProcessorAction;

use XF\BbCode\Parser;
use XF\BbCode\RuleSet;

class MentionUsers extends XFCP_MentionUsers
{
    protected $mentionedUserGroups = [];
    /**
     * @var array
     */
    protected $placeholders;

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
    /**
     * XF2.1.0
     *
     * @param $string
     * @return string|null
     */
    public function filterFinal($string)
    {
        $string = $this->extractMentionedUserGroups($string);

        if ($this->mentionedUserGroups)
        {
            $string = $this->setupPlaceholders($string,
                '#\[(usergroup)(=[^\]]*)?](.*)\[/\\1]#siU'
            );
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $string = parent::filterFinal($string);

        $string = $this->restorePlaceholders($string);

        return $string;
    }

    /**
     * XF2.1.1+
     *
     * @param string  $string
     * @param Parser  $parser
     * @param RuleSet $rules
     * @param array   $options
     * @return string|null
     */
    public function filterInput($string, Parser $parser, RuleSet $rules, array &$options)
    {
        $string = $this->extractMentionedUserGroups($string);

        if ($this->mentionedUserGroups)
        {
            $string = $this->setupPlaceholders($string,
                '#\[(usergroup)(=[^\]]*)?](.*)\[/\\1]#siU'
            );
        }

        $string = parent::filterInput($string, $parser, $rules, $options);

        $string = $this->restorePlaceholders($string);

        return $string;
    }

    /**
     * @param $string
     * @return string|null
     */
    protected function extractMentionedUserGroups($string)
    {
        /** @var \SV\UserMentionsImprovements\XF\Str\Formatter $formatter */
        $formatter = $this->formatter;
        if (!\is_callable([$formatter, 'getUserGroupMentionFormatter']))
        {
            \XF::logError('Add-on conflict detected, XF\Str\Formatter is not extended as expected', true);

            return $string;
        }
        /** @var \SV\UserMentionsImprovements\Str\UserGroupMentionFormatter $userGroupMentions */
        $userGroupMentions = $formatter->getUserGroupMentionFormatter();

        $string = $userGroupMentions->getMentionsBbCode($string);
        $this->mentionedUserGroups = $userGroupMentions->getMentionedUserGroups();

        return $string;
    }

    public function getMentionedUserGroups()
    {
        return $this->mentionedUserGroups;
    }
}
