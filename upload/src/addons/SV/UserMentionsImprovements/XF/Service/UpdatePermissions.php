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
     * @param string|bool|int                                                          $value
     * @param \XF\Entity\PermissionEntry|\XF\Entity\PermissionEntryContent|Entity|null $entry
     * @return \XF\Entity\PermissionEntry|\XF\Entity\PermissionEntryContent|Entity|null
     */
    protected function writeEntry(Permission $permission, $value, Entity $entry = null)
    {
        $oldState = ($this->userGroup && $entry) ? $entry->toArray() : null;

        /** @var Entity|\XF\Entity\PermissionEntry|\XF\Entity\PermissionEntryContent|null $newEntry */
        $newEntry = parent::writeEntry($permission, $value, $entry);

        if ($this->userGroup && ($newEntry !== null || $entry !== null))
        {
            if ($newEntry === null ||
                $entry === null ||
                $newEntry->getUniqueEntityId() !== $entry->getUniqueEntityId())
            {
                $this->hadChanges = true;
            }
            else if ($oldState && $oldState !== $newEntry->toArray())
            {
                $this->hadChanges = true;
            }
        }

        return $newEntry;
    }

    public function triggerCacheRebuild()
    {
        if ($this->userGroup)
        {
            if (!$this->hadChanges)
            {
                return;
            }
        }

        parent::triggerCacheRebuild();
    }
}
