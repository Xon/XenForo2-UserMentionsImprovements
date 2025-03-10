<?php

namespace SV\UserMentionsImprovements\XF\BbCode\ProcessorAction;

use SV\UserMentionsImprovements\XF\Str\Formatter as ExtendedFormatter;
use XF\Str\Formatter;

/**
 * @extends \XF\BbCode\ProcessorAction\MentionUsers
 * @property Formatter|ExtendedFormatter formatter
 */
class MentionUsers extends XFCP_MentionUsers
{
    protected $mentionedUserGroups = [];

    public function __construct(Formatter $formatter)
    {
        parent::__construct($formatter);
        // When calling getMentionsBbCode(), which transforms `@user => [user=ID]@user[/url]`;
        // XF2.0.0-2.1.0 calls it in filterFinal($string)
        // XF2.1.1-XF2.2.0B1 calls it in filterInput($string, Parser $parser, RuleSet $rules, array &$options)
        // XF2.2.0 Beta 2+ calls it in filterInput($string, \XF\BbCode\Processor $processor)
        // so just shim XF\Str\Formatter::getMentionFormatter to push user-groups into this object.
        // Note; getMentionFormatter() always returns a new stateful object, and isn't stored in \XF\BbCode\ProcessorAction\MentionUsers !
        // not great, but has the best compatibility...
        $this->formatter->svMentionUserGroup = $this;
    }

    /**
     * @return Formatter|ExtendedFormatter
     */
    public function getFormatter(): Formatter
    {
        return $this->formatter;
    }

    public function setMentionedUsers(array $mentionedUserGroups = [])
    {
        $this->mentionedUserGroups = $mentionedUserGroups;
    }

    public function getMentionedUserGroups(): array
    {
        // cleanup
        /** @var ExtendedFormatter $formatter */
        $formatter = $this->formatter;
        $formatter->svMentionUserGroup = null;

        return $this->mentionedUserGroups;
    }
}
