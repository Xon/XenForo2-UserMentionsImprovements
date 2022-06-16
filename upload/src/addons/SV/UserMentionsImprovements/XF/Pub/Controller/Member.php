<?php

namespace SV\UserMentionsImprovements\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;
use function count, strlen, ltrim, utf8_strlen;

class Member extends XFCP_Member
{
    public function actionUserGroup(ParameterBag $params)
    {
        $userGroupId = $params['user_group_id'];

        /** @var \SV\UserMentionsImprovements\XF\Entity\UserGroup $userGroup */
        $userGroup = $this->assertRecordExists('XF:UserGroup', $userGroupId);

        if (!$userGroup->canView())
        {
            if (\XF::options()->svUMIPermDeniedOnViewGroup ?? true)
            {
                return $this->noPermission();
            }

            return $this->notFound();
        }

        $filters = [];
        if ($this->request()->exists('_xfFilter'))
        {
            $filters = $this->filter('_xfFilter', [
                'text'   => 'str',
                'prefix' => 'bool',
                'page'   => 'uint'
            ]);

            $page = $this->filterPage($filters['page']);
            unset($filters['page']);
        }
        else
        {
            /** @noinspection PhpUndefinedFieldInspection */
            $page = $this->filterPage($params->page);
        }
        $perPage = (int)(\XF::options()->svUMI_usersPerPage ?? 50);

        /** @var \SV\UserMentionsImprovements\Repository\UserMentions $userMentionsRepo */
        $userMentionsRepo = \XF::app()->repository('SV\UserMentionsImprovements:UserMentions');
        $finder = $userMentionsRepo->findUsersByGroup($userGroup)
                                   ->limitByPage($page, $perPage);
        $this->applyUserGroupFilters($finder, $filters);

        $total = $finder->total();
        $this->assertValidPage($page, $perPage, $total, 'members/usergroup', $userGroup);

        $linkFilters = [];
        if (count($filters) !== 0)
        {
            $linkFilters['_xfFilter'] = $filters;
        }

        $users = $finder->fetch();

        $viewParams = [
            'users'     => $users,
            'userGroup' => $userGroup,

            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,

            'linkFilters' => $linkFilters,
            'filter' => $filters,
        ];

        return $this->view('SV\UserMentionsImprovements:Member\UserGroup', 'sv_members_usergroup', $viewParams);
    }

    protected function applyUserGroupFilters(\XF\Finder\User $finder, array &$filters)
    {
        if (strlen($filters['text'] ?? '') !== 0)
        {
            $hasPrefixSearch = (bool)($filters['prefix']  ?? false);
            if (!$hasPrefixSearch)
            {
                unset($filters['prefix']);
            }

            $finder->where(
                $finder->columnUtf8('username'),
                'LIKE',
                $finder->escapeLike(
                    $filters['text'],
                    $filters['prefix'] ? '?%' : '%?%'
                )
            );
        }
        else
        {
            unset($filters['text']);
            unset($filters['prefix']);
        }
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
                $userGroups = $userGroups->filterViewable();
            }
            else
            {
                $userGroups = [];
            }

            $response->setParam('userGroups', $userGroups);
        }

        return $response;
    }
}
