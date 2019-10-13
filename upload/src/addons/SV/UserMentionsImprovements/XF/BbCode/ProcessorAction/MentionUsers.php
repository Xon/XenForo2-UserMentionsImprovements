<?php

namespace SV\UserMentionsImprovements\XF\BbCode\ProcessorAction;

use XF\BbCode\Parser;
use XF\BbCode\RuleSet;

class MentionUsers extends XFCP_MentionUsers
{
    protected $mentionedUserGroups = [];

    /**
     * XF2.0- XF2.1
     *
     * @param $string
     * @return string|null
     */
    public function filterFinal($string)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $string = parent::filterFinal($string);
        $string = $this->extractMentionedUserGroups($string);

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
        /** @noinspection PhpUndefinedMethodInspection */
        $string = parent::filterInput($string, $parser, $rules, $options);
        $string = $this->extractMentionedUserGroups($string);

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
