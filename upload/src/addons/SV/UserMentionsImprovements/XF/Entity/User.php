<?php

namespace SV\UserMentionsImprovements\XF\Entity;

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
        return $this->canReceiveMentionEmails() && $this->Option->sv_email_on_mention;
    }

    public function receivesQuoteEmails()
    {
        return $this->canReceiveQuoteEmails() && $this->Option->sv_email_on_quote;
    }

    public function canMentionUserGroup()
    {
        return $this->hasPermission('general', 'sv_MentionUserGroup');
    }
}
