<?php

namespace SV\UserMentionsImprovements\XF\BbCode;

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
