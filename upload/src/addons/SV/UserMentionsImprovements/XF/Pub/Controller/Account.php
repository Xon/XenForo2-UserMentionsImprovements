<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Pub\Controller;

use SV\UserMentionsImprovements\XF\Entity\User as ExtendedUserEntity;
use XF\Entity\User as UserEntity;
use XF\Mvc\FormAction;

/**
 * @extends \XF\Pub\Controller\Account
 */
class Account extends XFCP_Account
{
    protected function savePrivacyProcess(UserEntity $visitor)
    {
        $form = parent::savePrivacyProcess($visitor);

        $this->svEmailSaveProcess($visitor, $form);

        return $form;
    }

    protected function accountDetailsSaveProcess(UserEntity $visitor)
    {
        $form = parent::accountDetailsSaveProcess($visitor);

        $this->svEmailSaveProcess($visitor, $form);

        return $form;
    }

    protected function preferencesSaveProcess(UserEntity $visitor)
    {
        $form = parent::preferencesSaveProcess($visitor);

        $this->svEmailSaveProcess($visitor, $form);

        return $form;
    }

    protected function svEmailSaveProcess(UserEntity $visitor, FormAction $form)
    {
        /** @var ExtendedUserEntity $visitor */
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
