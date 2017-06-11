<?php

namespace SV\UserMentionsImprovements;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

	public function installStep1()
	{
		$this->schemaManager()->alterTable('xf_user_group', function (Alter $table)
		{
			$table->addColumn('sv_mentionable', 'bool')->setDefault(0);
			$table->addColumn('sv_private', 'bool')->setDefault(0);
			$table->addColumn('sv_avatar_s', 'text')->nullable()->setDefault(null);
			$table->addColumn('sv_avatar_l', 'text')->nullable()->setDefault(null);
			$table->addColumn('sv_avatar_edit_date', 'int')->setDefault(0);
		});

		$this->schemaManager()->alterTable('xf_user_option', function (Alter $table)
		{
			$table->addColumn('sv_email_on_mention', 'bool')->setDefault(0);
			$table->addColumn('sv_email_on_quote', 'bool')->setDefault(0);
		});
	}

	// Upgrade steps add 1 to the installed version - the version should be equal to the version being installed.
	public function upgrade2000070Step1()
	{
		$this->schemaManager()->alterTable('xf_user_group', function (Alter $table)
		{
			$table->renameColumn('sv_taggable', 'sv_mentionable');
			$table->renameColumn('last_edit_date', 'sv_avatar_edit_date');
		});

		$this->schemaManager()->alterTable('xf_user_option', function (Alter $table)
		{
			$table->renameColumn('sv_email_on_tag', 'sv_email_on_mention');
		});
	}

	public function uninstallStep1()
	{
		$this->schemaManager()->alterTable('xf_user_group', function (Alter $table)
		{
			$table->dropColumns(['sv_mentionable', 'sv_private', 'sv_avatar_s', 'sv_avatar_l', 'sv_avatar_edit_date']);
		});
	}
}