<?php

namespace SV\UserMentionsImprovements\XF\Job;


/**
 * Extends \XF\Job\PermissionRebuild
 */
class PermissionRebuild extends XFCP_PermissionRebuild
{
    protected $extraDefaultData = [
        'steps'          => 0,
        'combinationId'  => 0,
        'cleaned'        => false,
        'batch'          => 100,
        'combinationIds' => [],
    ];

    protected function setupData(array $data)
    {
        $data = array_merge($this->extraDefaultData, $data);

        return parent::setupData($data);
    }

    public function run($maxRunTime)
    {
        $start = microtime(true);

        if (!$this->data['cleaned'])
        {
            /** @var \XF\Repository\PermissionCombination $combinationRepo */
            $combinationRepo = $this->app->repository('XF:PermissionCombination');
            $combinationRepo->deleteUnusedPermissionCombinations();

            $this->data['cleaned'] = true;
        }

        $this->data['steps']++;

        $app = \XF::app();

        $done = 0;
        $finder = \XF::finder('XF:PermissionCombination')
                     ->where('permission_combination_id', '>', $this->data['combinationId'])
                     ->order('permission_combination_id')
                     ->limit($this->data['batch']);
        if ($this->data['combinationIds'])
        {
            $finder->whereId($this->data['combinationIds']);
        }

        $combinations = $finder->fetch();
        if (!$combinations->count())
        {
            // there are situations where we run this job but not with this unique key, so this is unnecessary
            $this->app->jobManager()->cancelUniqueJob('permissionRebuild');

            return $this->complete();
        }

        $permissionBuilder = $app->permissionBuilder();

        foreach ($combinations AS $combination)
        {
            /** @var \XF\Entity\PermissionCombination $combination */
            $this->data['combinationId'] = $combination->permission_combination_id;

            $permissionBuilder->rebuildCombination($combination);
            $done++;

            if (microtime(true) - $start >= $maxRunTime)
            {
                break;
            }
        }

        $this->data['batch'] = $this->calculateOptimalBatch($this->data['batch'], $done, $start, $maxRunTime, 500);

        return $this->resume();
    }

    public function getStatusMessage()
    {
        $actionPhrase = \XF::phrase('rebuilding');
        $typePhrase = \XF::phrase('permissions');

        return sprintf('%s... %s %s', $actionPhrase, $typePhrase, str_repeat('. ', $this->data['steps']));
    }
}
