<?php

namespace SV\UserMentionsImprovements\XF\Admin\Controller;

use XF\Job\PermissionRebuild as PermissionRebuildJob;
use XF\Mvc\Reply\Redirect;

/**
 * @extends \XF\Admin\Controller\Tools
 */
class Tools extends XFCP_Tools
{
    public function actionCleanUpPermissions()
    {
        $response = parent::actionCleanUpPermissions();
        if ($response instanceof Redirect)
        {
            if ($this->filter('sv_rebuild_perms', 'bool'))
            {
                \XF::app()->jobManager()->enqueueUnique('permissionRebuild', PermissionRebuildJob::class);
            }
        }

        return $response;
    }
}