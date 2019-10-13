<?php

namespace SV\UserMentionsImprovements\XF\Admin\Controller;


use XF\Mvc\Reply\Redirect;

/**
 * Extends \XF\Admin\Controller\Tools
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
                $this->app->jobManager()->enqueueUnique('permissionRebuild', 'XF:PermissionRebuild');
            }
        }

        return $response;
    }
}