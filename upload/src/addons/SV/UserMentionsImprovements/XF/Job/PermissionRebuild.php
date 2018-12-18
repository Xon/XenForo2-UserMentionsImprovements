<?php

namespace SV\UserMentionsImprovements\XF\Job;


/**
 * Extends \XF\Job\PermissionRebuild
 */
class PermissionRebuild extends XFCP_PermissionRebuild
{
    protected $extraDefaultData = [
        'steps' => 0,
        'combinationId' => 0,
        'cleaned' => false,
        'batch' => 5,
        'combinationIds' => [],
    ];

    protected function setupData(array $data)
    {
        $data = array_merge($this->extraDefaultData, $data);

        return parent::setupData($data);
    }

    public function run($maxRunTime)
    {
        if (!$this->data['combinationIds'])
        {
            return parent::run($maxRunTime);
        }

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

        $permissionBuilder = $app->permissionBuilder();

        $done = 0;
        $combinations = \XF::finder('XF:PermissionCombination')
                           ->whereId($this->data['combinationIds'])
                           ->where('permission_combination_id','>', $this->data['combinationId'])
                           ->order('permission_combination_id')
                           ->fetch();
        if (count($combinations) == 0)
        {
            return $this->complete();
        }
        foreach ($combinations AS $combination)
        {
            if (microtime(true) - $start >= $maxRunTime)
            {
                break;
            }

            /** @var \XF\Entity\PermissionCombination $combination */
            $this->data['combinationId'] = $combination->permission_combination_id;

            $permissionBuilder->rebuildCombination($combination);
            $done++;
        }

        $this->data['batch'] = $this->calculateOptimalBatch($this->data['batch'], $done, $start, $maxRunTime, 20);

        return $this->resume();
    }

    public function getStatusMessage()
    {
        $actionPhrase = \XF::phrase('rebuilding');
        $typePhrase = \XF::phrase('permissions');
        return sprintf('%s... %s %s', $actionPhrase, $typePhrase, str_repeat('. ', $this->data['steps']));
    }
}
