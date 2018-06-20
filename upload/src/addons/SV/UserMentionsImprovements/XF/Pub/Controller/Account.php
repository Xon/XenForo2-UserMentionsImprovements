<?php

namespace SV\UserMentionsImprovements\XF\Pub\Controller;

use XF\Entity\User;

class Account extends XFCP_Account
{
    protected function accountDetailsSaveProcess(User $visitor)
    {
        $form = parent::accountDetailsSaveProcess($visitor);

        $this->svEmailSaveProcess($visitor, $form);

        return $form;
    }

    protected function preferencesSaveProcess(User $visitor)
    {
        $form = parent::preferencesSaveProcess($visitor);

        $this->svEmailSaveProcess($visitor, $form);

        return $form;
    }

    protected function svEmailSaveProcess(/** @noinspection PhpUnusedParameterInspection */
        User $visitor, \XF\Mvc\FormAction $form)
    {
        $options = [];
        if (\XF::options()->sv_send_email_on_tagging)
        {
            $options['sv_email_on_mention'] = 'bool';
        }
        if (\XF::options()->sv_send_email_on_quote)
        {
            $options['sv_email_on_quote'] = 'bool';
        }

        if ($options)
        {
            $input = $this->filter(
                [
                    'option' => $options
                ]
            );

            /** @var \SV\UserMentionsImprovements\XF\Entity\User $visitor */
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
    }
}
