<?php

namespace SV\UserMentionsImprovements\XF\Service;



/**
 * Extends \XF\Service\UpdatePermissions
 */
class UpdatePermissions extends XFCP_UpdatePermissions
{
    public function triggerCacheRebuild()
    {
        /** @var \XF\Repository\PermissionCombination $combinationRepo */
        $combinationRepo = $this->repository('XF:PermissionCombination');

        if ($this->userGroup)
        {
            $combinations = $combinationRepo->getPermissionCombinationsForUserGroup($this->userGroup->user_group_id);
            if (count($combinations) > 8)
            {
                $combinationIds = $combinations->pluckNamed('permission_combination_id');
                // too much to build inline
                $this->app->jobManager()->enqueueUnique('permissionRebuild', 'XF:PermissionRebuild', ['combinationIds' => $combinationIds]);

                return;
            }
        }

        parent::triggerCacheRebuild();
    }
}
