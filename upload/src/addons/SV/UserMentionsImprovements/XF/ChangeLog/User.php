<?php

namespace SV\UserMentionsImprovements\XF\ChangeLog;



/**
 * Extends \XF\ChangeLog\User
 */
class User extends XFCP_User
{
    protected function getLabelMap()
    {
        $map = parent::getLabelMap();
        $map['sv_email_on_quote'] = 'sv_receive_email_when_quoted';
        $map['sv_email_on_mention'] = 'sv_receive_email_when_mentioned';

        return $map;
    }

    protected function getFormatterMap()
    {
        $map = parent::getFormatterMap();
        $map['sv_email_on_quote'] = 'formatYesNo';
        $map['sv_email_on_mention'] = 'formatYesNo';

        return $map;
    }
}