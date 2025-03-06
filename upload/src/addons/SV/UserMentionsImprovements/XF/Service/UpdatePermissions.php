<?php

namespace SV\UserMentionsImprovements\XF\Service;

use XF\Entity\Permission as PermissionEntity;
use XF\Entity\PermissionEntry as PermissionEntryEntity;
use XF\Entity\PermissionEntryContent as PermissionEntryContentEntity;
use XF\Mvc\Entity\Entity;

/**
 * @extends \XF\Service\UpdatePermissions
 */
class UpdatePermissions extends XFCP_UpdatePermissions
{
    protected $hadChanges = false;

    /**
     * @param PermissionEntity                                               $permission
     * @param string|bool|int                                                $value
     * @param PermissionEntryEntity|PermissionEntryContentEntity|Entity|null $entry
     * @return PermissionEntryEntity|PermissionEntryContentEntity|Entity|null
     */
    protected function writeEntry(PermissionEntity $permission, $value, ?Entity $entry = null)
    {
        $oldState = ($this->userGroup && $entry) ? $entry->toArray() : null;

        /** @var Entity|PermissionEntryEntity|PermissionEntryContentEntity|null $newEntry */
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
