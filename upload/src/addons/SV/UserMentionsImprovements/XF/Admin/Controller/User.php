<?php

namespace SV\UserMentionsImprovements\XF\Admin\Controller;

/**
 * Extends \XF\Admin\Controller\User
 */
class User extends XFCP_User
{
    protected function userSaveProcess(\XF\Entity\User $user)
    {
        $form = parent::userSaveProcess($user);

        $input = $this->filter(
            [
                'option' => [
                    'sv_email_on_mention' => 'bool',
                    'sv_email_on_quote'   => 'bool',
                ],
            ]
        );

        $userOptions = $user->getRelationOrDefault('Option');
        $form->setupEntityInput($userOptions, $input['option']);

        return $form;
    }
}
