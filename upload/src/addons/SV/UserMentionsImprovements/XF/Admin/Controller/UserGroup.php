<?php

namespace SV\UserMentionsImprovements\XF\Admin\Controller;

class UserGroup extends XFCP_UserGroup
{
    protected function userGroupSaveProcess(\XF\Entity\UserGroup $userGroup)
    {
        $form = parent::userGroupSaveProcess($userGroup);

        $input = $this->filter(
            [
                'sv_mentionable' => 'bool',
                'sv_private'     => 'bool',
                'sv_avatar_s'    => 'str',
                'sv_avatar_l'    => 'str',
            ]
        );

        $form->setup(
            function () use ($userGroup, $input) {
                $userGroup->bulkSet($input);
            }
        );

        return $form;
    }
}
