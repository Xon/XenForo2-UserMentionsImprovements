<?php

namespace SV\UserMentionsImprovements\XF\Permission;



/**
 * Extends \XF\Permission\Builder
 */
class Builder extends XFCP_Builder
{
    public function rebuildCombinationContent(\XF\Entity\PermissionCombination $combination, array $basePerms)
    {
        // trigger loading of entries content permission entries, then nuke
        $this->getContentHandlers();
        \XF::db()->emptyTable('xf_permission_entry_content');
        parent::rebuildCombinationContent($combination, $basePerms);
    }
}