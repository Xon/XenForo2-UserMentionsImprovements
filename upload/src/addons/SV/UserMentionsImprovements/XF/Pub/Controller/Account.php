<?php

namespace SV\UserMentionsImprovements\XF\Pub\Controller;

use XF\Entity\User;
use XF\Mvc\FormAction;

class Account extends XFCP_Account
{
    protected function savePrivacyProcess(User $visitor)
    {
        $form = parent::savePrivacyProcess($visitor);

        $this->svEmailSaveProcess($visitor, $form);

        return $form;
    }

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

    protected function svEmailSaveProcess(User $visitor, FormAction $form)
    {
        /** @var \SV\UserMentionsImprovements\XF\Entity\User $visitor */
        $options = [];
        if ($visitor->canReceiveMentionEmails())
        {
            $options['sv_email_on_mention'] = 'bool';
        }
        if ($visitor->canReceiveQuoteEmails())
        {
            $options['sv_email_on_quote'] = 'bool';
        }

        if ($options)
        {
            $input = $this->filter(
                [
                    'option' => $options,
                ]
            );

            if ($input['option'])
            {
                $userOptions = $visitor->getRelationOrDefault('Option');
                $form->setupEntityInput($userOptions, $input['option']);
            }
        }
    }
}
