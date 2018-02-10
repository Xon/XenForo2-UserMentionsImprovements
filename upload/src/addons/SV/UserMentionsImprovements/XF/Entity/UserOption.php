<?php

namespace SV\UserMentionsImprovements\XF\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * @property bool sv_email_on_mention
 * @property bool sv_email_on_quote
 */
class UserOption extends XFCP_UserOption
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['sv_email_on_mention'] = ['type' => Entity::BOOL, 'default' => 0];
        $structure->columns['sv_email_on_quote'] = ['type' => Entity::BOOL, 'default' => 0];

        return $structure;
    }
}
