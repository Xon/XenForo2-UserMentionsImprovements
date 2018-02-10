<?php

namespace SV\UserMentionsImprovements\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

class Member extends XFCP_Member
{
    public function actionUsergroup(ParameterBag $params)
    {
        $userGroupId = $params['user_group_id'];

        /** @var \SV\UserMentionsImprovements\XF\Entity\UserGroup $userGroup */
        $userGroup = $this->assertRecordExists('XF:UserGroup', $userGroupId);

        if (!$userGroup->canView())
        {
            return $this->noPermission();
        }

        /** @var \SV\UserMentionsImprovements\Repository\UserMentions $userMentionsRepo */
        $userMentionsRepo = \XF::app()->repository('SV\UserMentionsImprovements:UserMentions');
        $users = $userMentionsRepo->getMembersOfUserGroup($userGroup);

        $viewParams = [
            'users'     => $users,
            'userGroup' => $userGroup
        ];

        return $this->view('SV\UserMentionsImprovements:Member\UserGroup', 'sv_usermentionsimprovements_usergroup_view', $viewParams);
    }

    public function actionFind()
    {
        $response = parent::actionFind();

        if ($response instanceof View)
        {
            /** @var \SV\UserMentionsImprovements\XF\Entity\User $visitor */
            $visitor = \XF::visitor();

            $q = ltrim($this->filter('q', 'str', ['no-trim']));

            if ($visitor->canMentionUserGroup() && $q !== '' && utf8_strlen($q) >= 2)
            {
                /** @var \SV\UserMentionsImprovements\XF\Finder\UserGroup $userGroupFinder */
                $userGroupFinder = $this->finder('XF:UserGroup');
                $userGroupFinder->mentionableGroups($q);
                $userGroups = $userGroupFinder->fetch();
                $userGroups = $userGroupFinder->filterMentionableGroup($userGroups);
            }
            else
            {
                $userGroups = [];
            }

            $response->setParam('usergroups', $userGroups);
        }

        return $response;
    }
}
