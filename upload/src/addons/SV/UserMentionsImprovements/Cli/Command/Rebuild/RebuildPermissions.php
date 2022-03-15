<?php

namespace SV\UserMentionsImprovements\Cli\Command\Rebuild;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use XF\Cli\Command\Rebuild\AbstractRebuildCommand;

class RebuildPermissions extends AbstractRebuildCommand
{
    protected function getRebuildName(): string
    {
        return 'sv-permissions';
    }

    protected function getRebuildDescription(): string
    {
        return 'Rebuilds all permissions';
    }

    protected function getRebuildClass(): string
    {
        \XF::db()->logQueries(false); // need to limit memory usage

        return 'XF:PermissionRebuild';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var \XF\Repository\PermissionCombination $permComboRepo */
        $permComboRepo = \XF::repository('XF:PermissionCombination');

        /** @var \XF\Repository\PermissionEntry $permEntryRepo */
        $permEntryRepo = \XF::repository('XF:PermissionEntry');

        $permEntryRepo->deleteOrphanedGlobalUserPermissionEntries();
        $permEntryRepo->deleteOrphanedContentUserPermissionEntries();

        $permComboRepo->deleteUnusedPermissionCombinations();

        $this->setupAndRunJob(
            'permissionRebuild',
            $this->getRebuildClass(),
            [], $output
        );

        return 0;
    }
}