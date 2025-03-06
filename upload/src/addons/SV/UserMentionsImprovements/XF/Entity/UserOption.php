<?php

namespace SV\UserMentionsImprovements\XF\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * @extends \XF\Entity\UserOption
 * @property bool $sv_email_on_mention
 * @property bool $sv_email_on_quote
 */
class UserOption extends XFCP_UserOption
{
    protected function _setupDefaults()
    {
        parent::_setupDefaults();

        $defaults = \XF::options()->registrationDefaults;
        $this->sv_email_on_mention = (bool)($defaults['sv_email_on_mention'] ?? false);
        $this->sv_email_on_quote = (bool)($defaults['sv_email_on_quote'] ?? false);
    }

    /** @noinspection PhpMissingReturnTypeInspection */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['sv_email_on_mention'] = ['type' => Entity::BOOL, 'default' => 0];
        $structure->columns['sv_email_on_quote'] = ['type' => Entity::BOOL, 'default' => 0];

        return $structure;
    }
}
