<?php

namespace SV\UserMentionsImprovements\XF\Entity;

use SV\UserMentionsImprovements\Entity\UserGroupRelation;
use XF\Entity\Post;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * @property-read UserGroupRelation $UserGroupRelations
 */
class User extends XFCP_User
{
    public function canReceiveMentionEmails(): bool
    {
        return (\XF::options()->sv_send_email_on_tagging ?? false) && $this->hasPermission('general', 'sv_ReceiveMentionEmails');
    }

    public function canReceiveQuoteEmails(): bool
    {
        return (\XF::options()->sv_send_email_on_quote ?? false) && $this->hasPermission('general', 'sv_ReceiveQuoteEmails');
    }

    public function receivesMentionEmails(): bool
    {
        if (!$this->canReceiveMentionEmails())
        {
            return false;
        }

        /** @var UserOption $option */
        $option = $this->Option;

        return $option->sv_email_on_mention;
    }

    public function receivesQuoteEmails(): bool
    {
        if (!$this->canReceiveQuoteEmails())
        {
            return false;
        }

        /** @var UserOption $option */
        $option = $this->Option;

        return $option->sv_email_on_quote;
    }

    protected function _getMentionContentTypeAndId(Entity $messageEntity = null): array
    {
        if ($messageEntity instanceof Post)
        {
            $thread = $messageEntity->Thread;
            if ($thread)
            {
                return ['node', $thread->node_id];
            }
        }

        return [null, null];
    }

    public function canMention(Entity $messageEntity = null): bool
    {
        list($contentType, $contentId) = $this->_getMentionContentTypeAndId($messageEntity);

        if ($contentType !== null && $contentId)
        {
            return $this->hasContentPermission($contentType, $contentId, 'sv_EnableMentions');
        }

        return true;
    }

    public function canMentionUserGroup(): bool
    {
        return $this->hasPermission('general', 'sv_MentionUserGroup');
    }

    /** @noinspection PhpMissingReturnTypeInspection */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->relations['UserGroupRelations'] = [
            'entity'     => 'SV\UserMentionsImprovements:UserGroupRelation',
            'type'       => Entity::TO_ONE,
            'conditions' => 'user_id',
        ];

        return $structure;
    }
}
