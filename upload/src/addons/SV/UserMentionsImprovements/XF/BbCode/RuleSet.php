<?php

namespace SV\UserMentionsImprovements\XF\BbCode;

/**
 * @extends \XF\BbCode\RuleSet
 */
class RuleSet extends XFCP_RuleSet
{
    public function addDefaultTags()
    {
        parent::addDefaultTags();

        $this->addTag(
            'usergroup',
            [
                'hasOption'    => true,
                'plain'        => true,
                'stopSmilies'  => true,
                'stopAutoLink' => true,
            ]
        );
    }
}
