<?php

namespace SV\UserMentionsImprovements\XF\Entity;

use SV\UserMentionsImprovements\Entity\UserGroupRelation;
use XF\Entity\Post;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 *
 * @property UserGroupRelation UserGroupRelations
 */
class User extends XFCP_User
{
    public function canReceiveMentionEmails()
    {
        return \XF::options()->sv_send_email_on_tagging && $this->hasPermission('general', 'sv_ReceiveMentionEmails');
    }

    public function canReceiveQuoteEmails()
    {
        return \XF::options()->sv_send_email_on_quote && $this->hasPermission('general', 'sv_ReceiveQuoteEmails');
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

    protected function _getMentionContentTypeAndId(Entity $messageEntity = null)
    {
        if ($messageEntity instanceof Post && $messageEntity->Thread)
        {
            return ['node', $messageEntity->Thread->node_id];
        }

        return [null, null];
    }

    public function canMention(Entity $messageEntity = null)
    {
        list($contentType, $contentId) = $this->_getMentionContentTypeAndId($messageEntity);

        if ($contentType && $contentId)
        {
            return $this->hasContentPermission($contentType, $contentId, 'sv_EnableMentions');
        }

        return true;
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
            'type'       => Entity::TO_ONE,
            'conditions' => 'user_id'
        ];

        return $structure;
    }
}
