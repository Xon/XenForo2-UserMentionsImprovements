<?php

namespace SV\UserMentionsImprovements\XF\Pub\Controller;

use SV\StandardLib\Helper;
use SV\UserMentionsImprovements\Repository\UserMentions as UserMentionsRepo;
use SV\UserMentionsImprovements\XF\Entity\User as ExtendedUserEntity;
use SV\UserMentionsImprovements\XF\Entity\UserGroup as ExtendedUserGroupEntity;
use SV\UserMentionsImprovements\XF\Finder\UserGroup as ExtendedUserGroupFinder;
use XF\Finder\User as UserFinder;
use XF\Finder\UserGroup as UserGroupFinder;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;
use function count, strlen, ltrim;

/**
 * @extends \XF\Pub\Controller\Member
 */
class Member extends XFCP_Member
{
    public function actionUserGroup(ParameterBag $params)
    {
        $userGroupId = $params['user_group_id'];

        /** @var ExtendedUserGroupEntity $userGroup */
        $userGroup = $this->assertRecordExists('XF:UserGroup', $userGroupId);

        if (!$userGroup->canView())
        {
            if (\XF::options()->svUMIPermDeniedOnViewGroup ?? true)
            {
                return $this->noPermission();
            }

            return $this->notFound();
        }

        $filters = $this->getUserGroupFilters();
        $page = $this->filterPage($params['page'] ?? 0);
        $perPage = \XF::options()->svUMI_usersPerPage ?? 50;

        $finder = UserMentionsRepo::get()
                                  ->findUsersByGroup($userGroup)
                                  ->limitByPage($page, $perPage);
        $this->applyUserGroupFilters($finder, $filters);

        $total = $finder->total();
        $this->assertValidPage($page, $perPage, $total, 'members/usergroup', $userGroup);

        $linkFilters = [];
        if (count($filters) !== 0)
        {
            $linkFilters['_xfFilter'] = $filters;
        }

        $finalUrl = $this->buildLink('full:members/usergroup', $userGroup, $linkFilters + ($page > 1 ? ['page' => $page] : []));
        $addParamsToPageNav = $this->filter('_xfWithData', 'bool');

        $users = $finder->fetch();

        $viewParams = [
            'users'     => $users,
            'userGroup' => $userGroup,

            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,

            'addParamsToPageNav' => $addParamsToPageNav,
            'linkFilters' => $linkFilters,
            'filter' => $filters,
            'finalUrl' => $finalUrl,
        ];

        return $this->view('SV\UserMentionsImprovements:Member\UserGroup', 'sv_members_usergroup', $viewParams);
    }

    protected function getUserGroupFilters(): array
    {
        if ($this->request()->exists('_xfFilter'))
        {
            return $this->filter('_xfFilter', [
                'text'   => 'str',
                'prefix' => 'bool',
            ]);
        }

        return [];
    }

    protected function applyUserGroupFilters(UserFinder $finder, array &$filters)
    {
        if (strlen($filters['text'] ?? '') !== 0)
        {
            $hasPrefixSearch = (bool)($filters['prefix']  ?? true);
            if (!$hasPrefixSearch)
            {
                unset($filters['prefix']);
            }

            $finder->where(
                $finder->columnUtf8('username'),
                'LIKE',
                $finder->escapeLike(
                    $filters['text'],
                    $hasPrefixSearch ? '?%' : '%?%'
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
            /** @var ExtendedUserEntity $visitor */
            $visitor = \XF::visitor();

            $q = ltrim($this->filter('q', 'str', ['no-trim']));

            if ($visitor->canMentionUserGroup() && $q !== '' && \mb_strlen($q) >= 2)
            {
                /** @var ExtendedUserGroupFinder $userGroupFinder */
                $userGroupFinder = Helper::finder(UserGroupFinder::Class);
                $userGroupFinder->mentionableGroups($q);
                $userGroups = $userGroupFinder->fetch();
                $userGroups = $userGroups->filter(function (ExtendedUserGroupEntity $userGroup) {
                    return $userGroup->canViewForAutocomplete();
                });
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
