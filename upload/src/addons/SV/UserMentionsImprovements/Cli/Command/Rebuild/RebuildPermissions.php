<?php

namespace SV\UserMentionsImprovements\Cli\Command\Rebuild;

use SV\StandardLib\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use XF\Cli\Command\Rebuild\AbstractRebuildCommand;
use XF\Job\PermissionRebuild;
use XF\Repository\PermissionCombination as PermissionCombinationRepo;
use XF\Repository\PermissionEntry as PermissionEntryRepo;

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

        return PermissionRebuild::class;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $permComboRepo = Helper::repository(PermissionCombinationRepo::class);
        $permEntryRepo = Helper::repository(PermissionEntryRepo::class);

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