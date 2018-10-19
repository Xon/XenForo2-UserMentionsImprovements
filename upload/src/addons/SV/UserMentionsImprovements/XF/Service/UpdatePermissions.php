<?php

namespace SV\UserMentionsImprovements\XF\Service;
use XF\Entity\Permission;
use XF\Mvc\Entity\Entity;


/**
 * Extends \XF\Service\UpdatePermissions
 */
class UpdatePermissions extends XFCP_UpdatePermissions
{
    protected $hadChanges = false;

    /**
     * @param Permission                                                               $permission
     * @param mixed                                                                    $value
     * @param Entity|\XF\Entity\PermissionEntry|\XF\Entity\PermissionEntryContent|null $entry
     * @return null|\XF\Entity\PermissionEntry|\XF\Entity\PermissionEntry|\XF\Entity\PermissionEntryContent|Entity
     * @throws \XF\PrintableException
     */
    protected function writeEntry(Permission $permission, $value, Entity $entry = null)
    {
        if ($value == 'unset' || $value === '0' || $value === 0)
        {
            if ($entry)
            {
                $this->hadChanges = true;
                $entry->delete();
            }

            return null;
        }

        if (!$entry)
        {
            if ($this->contentType)
            {
                /** @var \XF\Entity\PermissionEntryContent $entry */
                $entry = $this->em()->create('XF:PermissionEntryContent');
                $entry->content_type = $this->contentType;
                $entry->content_id = $this->contentId;
            }
            else
            {
                /** @var \XF\Entity\PermissionEntry $entry */
                $entry = $this->em()->create('XF:PermissionEntry');
            }

            $entry->permission_group_id = $permission->permission_group_id;
            $entry->permission_id = $permission->permission_id;
        }

        $entry->user_id = $this->user ? $this->user->user_id : 0;
        $entry->user_group_id = $this->userGroup ? $this->userGroup->user_group_id : 0;

        if ($permission->permission_type == 'integer')
        {
            $entry->permission_value = 'use_int';
            $entry->permission_value_int = intval($value);
        }
        else
        {
            $entry->permission_value = $value;
            $entry->permission_value_int = 0;
        }

        $entry->saveIfChanged($saved);
        if ($saved)
        {
            $this->hadChanges = true;
        }

        return $entry;
    }

    public function triggerCacheRebuild()
    {
        if (!$this->hadChanges)
        {
            return;
        }

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
