<?php

namespace SV\UserMentionsImprovements\XF\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class User extends XFCP_User
{
    public function canReceiveMentionEmails()
    {
        return \XF::options()->sv_send_email_on_tagging && $this->hasPermission('general', 'sv_ReceiveMentionEmails');
    }

    public function canReceiveQuoteEmails()
    {
        return \XF::options()->sv_send_email_on_tagging && $this->hasPermission('general', 'sv_ReceiveQuoteEmails');
    }

    public function receivesMentionEmails()
    {
        if (!$this->canReceiveMentionEmails())
        {
            return false;
        }

        /** @var UserOption $option */
        $option = $this->Option;

        return $option->sv_email_on_mention;
    }

    public function receivesQuoteEmails()
    {
        if (!$this->canReceiveQuoteEmails())
        {
            return false;
        }

        /** @var UserOption $option */
        $option = $this->Option;

        return $option->sv_email_on_quote;
    }

    public function canMentionUserGroup()
    {
        return $this->hasPermission('general', 'sv_MentionUserGroup');
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->relations['UserGroupRelations'] = [
            'entity'     => 'SV\UserMentionsImprovements:UserGroupRelation',
            'type'       => Entity::TO_MANY,
            'conditions' => 'user_id'
        ];

        return $structure;
    }
}
