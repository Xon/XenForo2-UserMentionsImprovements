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
     * @return null|\XF\Entity\PermissionEntry|\XF\Entity\PermissionEntryContent|Entity
     */
    protected function writeEntry(Permission $permission, $value, Entity $entry = null)
    {
        $oldState = ($this->userGroup && $entry) ? $entry->toArray() : null;

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
