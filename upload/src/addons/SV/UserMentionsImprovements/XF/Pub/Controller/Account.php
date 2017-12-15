<?php

namespace SV\UserMentionsImprovements\XF\Pub\Controller;

class Account extends XFCP_Account
{
    protected function preferencesSaveProcess(\XF\Entity\User $visitor)
    {
        $form = parent::preferencesSaveProcess($visitor);

        if (\XF::options()->sv_send_email_on_tagging)
        {
            $input = $this->filter(
                [
                    'option' => [
                        'sv_email_on_mention' => 'bool',
                        'sv_email_on_quote'   => 'bool'
                    ]
                ]
            );

            /** @var \SV\UserMentionsImprovements\XF\Entity\User $visitor */
            $visitor = \XF::visitor();
            /** @var \SV\UserMentionsImprovements\XF\Entity\UserOption $option */
            $option = $visitor->Option;

            if (!$visitor->canReceiveMentionEmails() && $option->sv_email_on_mention)
            {
                unset($input['option']['sv_email_on_mention']);
            }

            if (!$visitor->canReceiveQuoteEmails() && $option->sv_email_on_quote)
            {
                unset($input['option']['sv_email_on_quote']);
            }

            if ($input['option'])
            {
                $userOptions = $visitor->getRelationOrDefault('Option');
                $form->setupEntityInput($userOptions, $input['option']);
            }
        }

        return $form;
    }
}
