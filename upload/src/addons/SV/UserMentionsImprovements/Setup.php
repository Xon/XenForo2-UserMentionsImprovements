<?php

namespace SV\UserMentionsImprovements;

use SV\StandardLib\InstallerHelper;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Entity\Option as OptionEntity;
use XF\Entity\User;
use XF\Job\PermissionRebuild;

class Setup extends AbstractSetup
{
    use InstallerHelper;
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1(): void
    {
        $sm = $this->schemaManager();

        foreach ($this->getAlterTables() as $tableName => $callback)
        {
            if ($sm->tableExists($tableName))
            {
                $sm->alterTable($tableName, $callback);
            }
        }
    }

    public function installStep2(): void
    {
        $this->db()->query('
            UPDATE xf_user_group
            SET sv_avatar_edit_date = ?
            WHERE sv_avatar_edit_date = 0
        ', [\XF::$time]);
    }

    public function installStep3(): void
    {
        $this->applyRegistrationDefaults([
            'sv_email_on_mention' => '',
            'sv_email_on_quote'   => '',
        ]);
    }

    public function installStep4(): void
    {
        $this->defaultPermission();
    }

    public function upgrade1010000Step1(): void
    {
        /** @noinspection SqlResolve */
        $this->db()->query(
            '
                UPDATE xf_user_group
                SET last_edit_date = ?
                WHERE last_edit_date = 0
            ', [\XF::$time]
        );
    }

    public function upgrade104010Step1(): void
    {
        /** @noinspection SqlResolve */
        /** @noinspection SqlWithoutWhere */
        $this->db()->query(
            '
                UPDATE xf_user_option
                SET sv_email_on_quote = sv_email_on_tag
            '
        );
    }

    public function upgrade1000900Step1(): void
    {
        $db = $this->db();

        $db->query(
            "
                UPDATE xf_permission_entry
                SET permission_id = 'sv_EnableMentions', permission_value = 'deny'
                WHERE permission_id = 'sv_DisableTagging'
            "
        );
        $db->query(
            "
                UPDATE xf_permission_entry_content
                SET permission_id = 'sv_EnableMentions', permission_value = 'deny'
                WHERE permission_id = 'sv_DisableTagging'
            "
        );

        $this->defaultPermission();
    }

    // Upgrade steps add 1 to the installed version - the version should be equal to the version being installed.
    public function upgrade2000070Step1(): void
    {
        $this->schemaManager()->alterTable(
            'xf_user_group', function (Alter $table) {
            $table->renameColumn('sv_taggable', 'sv_mentionable');
            $table->renameColumn('last_edit_date', 'sv_avatar_edit_date');
        }
        );

        $this->schemaManager()->alterTable(
            'xf_user_option', function (Alter $table) {
            $table->renameColumn('sv_email_on_tag', 'sv_email_on_mention');
        }
        );

        /** @var OptionEntity $entity */
        $entity = \XF::finder('XF:Option')->where(['option_id', 'registrationDefaults'])->fetchOne();
        $registrationDefaults = $entity->option_value;
        if (isset($registrationDefaults['sv_email_on_tag']))
        {
            $registrationDefaults['sv_email_on_mention'] = $registrationDefaults['sv_email_on_tag'];
            unset($registrationDefaults['sv_email_on_tag']);
        }
        $entity->option_value = $registrationDefaults;
        $entity->saveIfChanged();
    }

    public function upgrade2000070Step2(): void
    {
        // rewrite permissions
        $db = $this->db();

        $db->query(
            "
                UPDATE xf_permission_entry
                SET permission_id = 'sv_EnableMentions'
                WHERE permission_id = 'sv_EnableTagging'
            "
        );
        $db->query(
            "
                UPDATE xf_permission_entry_content
                SET permission_id = 'sv_EnableMentions'
                WHERE permission_id = 'sv_EnableTagging'
            "
        );

        $db->query(
            "
                UPDATE xf_permission_entry
                SET permission_id = 'sv_MentionUserGroup'
                WHERE permission_id = 'sv_TagUserGroup'
            "
        );
        $db->query(
            "
                UPDATE xf_permission_entry_content
                SET permission_id = 'sv_MentionUserGroup'
                WHERE permission_id = 'sv_TagUserGroup'
            "
        );

        $db->query(
            "INSERT IGNORE INTO xf_permission_entry_content (content_type, content_id, user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
                SELECT DISTINCT content_type, content_id, user_group_id, user_id, 'forum', 'sv_ReceiveQuoteEmails', 'content_allow', 0
                FROM xf_permission_entry_content
                WHERE permission_id = 'sv_ReceiveTagAlertEmails'
            "
        );
        $db->query(
            "INSERT IGNORE INTO xf_permission_entry_content (content_type, content_id, user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
                SELECT DISTINCT content_type, content_id, user_group_id, user_id, 'forum', 'sv_ReceiveQuoteEmails', 'content_allow', 0
                FROM xf_permission_entry_content
                WHERE permission_id = 'sv_ReceiveTagAlertEmails'
            "
        );

        $db->query(
            "
                UPDATE xf_permission_entry
                SET permission_id = 'sv_ReceiveMentionEmails'
                WHERE permission_id = 'sv_ReceiveTagAlertEmails'
            "
        );
        $db->query(
            "
                UPDATE xf_permission_entry_content
                SET permission_id = 'sv_ReceiveMentionEmails'
                WHERE permission_id = 'sv_ReceiveTagAlertEmails'
            "
        );

        $this->app->jobManager()->enqueueUnique(
            'permissionRebuild',
            PermissionRebuild::class,
            [],
            false
        );
    }

    /**
     * Rewrite options
     */
    public function upgrade2000070Step3(): void
    {
        /** @var OptionEntity[] $options */
        $options = $this->app->finder('XF:Option')->whereIds(['sv_default_group_avatar_s', 'sv_default_group_avatar_l'])->fetch();
        foreach ($options as $option)
        {
            $value = $option->getOptionValue();
            if (\is_string($value))
            {
                $option->option_value = \str_replace('styles/default/sv/tagging/', 'styles/default/sv/mentionimprovements/', $value);
                $option->saveIfChanged();
            }
        }
    }

    public function upgrade2020000Step1(): void
    {
        $this->applyGlobalPermissionByGroup('general', 'sv_ViewPublicGroups', [User::GROUP_REG, User::GROUP_GUEST]);
    }

    public function upgrade2030400Step1(): void
    {
        $this->installStep1();
    }

    public function upgrade2040000Step1(): void
    {
        $this->app->jobManager()->enqueueUnique('permissionRebuild', PermissionRebuild::class, [], true);
    }

    public function upgrade2070700Step1(): void
    {
        $db = $this->db();
        $db->query("
            DELETE FROM xf_permission_entry
            WHERE permission_group_id = 'forum' AND permission_id IN (
                 'sv_ReceiveMentionEmails',
                 'sv_ReceiveQuoteEmails'
            )
        ");

        $db->query("
            DELETE FROM xf_permission_entry_content
            WHERE permission_group_id = 'forum' AND permission_id IN (
                 'sv_ReceiveMentionEmails',
                 'sv_ReceiveQuoteEmails'
            )
        ");
    }

    public function upgrade2080100Step1(): void
    {
        $this->installStep1();
    }

    public function uninstallStep1(): void
    {
        $sm = $this->schemaManager();

        foreach ($this->getRemoveAlterTables() as $tableName => $callback)
        {
            if ($sm->tableExists($tableName))
            {
                $sm->alterTable($tableName, $callback);
            }
        }
    }

    public function defaultPermission(): void
    {
        $db = $this->db();

        $db->query(
            "INSERT IGNORE INTO xf_permission_entry (user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
                SELECT DISTINCT user_group_id, user_id, 'forum', 'sv_EnableMentions', 'allow', 0
                FROM xf_permission_entry
                WHERE permission_group_id = 'general' AND permission_id IN ('maxMentionedUsers') AND permission_value_int <> 0
            "
        );
        $db->query(
            "INSERT IGNORE INTO xf_permission_entry_content (content_type, content_id, user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
                SELECT DISTINCT content_type, content_id, user_group_id, user_id, 'forum', 'sv_EnableMentions', 'content_allow', 0
                FROM xf_permission_entry_content
                WHERE permission_group_id = 'general' AND permission_id IN ('maxMentionedUsers') AND permission_value_int <> 0
            "
        );

        $this->applyGlobalPermissionByGroup('general', 'sv_ViewPublicGroups', [User::GROUP_REG, User::GROUP_GUEST]);
    }

    protected function getAlterTables(): array
    {
        return [
            'xf_user_group'  => function (Alter $table) {
                $this->addOrChangeColumn($table, 'sv_mentionable', 'bool')->setDefault(0);
                $this->addOrChangeColumn($table, 'sv_private', 'bool')->setDefault(1);
                $this->addOrChangeColumn($table, 'sv_avatar_s', 'text')->nullable()->setDefault(null);
                $this->addOrChangeColumn($table, 'sv_avatar_l', 'text')->nullable()->setDefault(null);
                $this->addOrChangeColumn($table, 'sv_avatar_edit_date', 'int')->setDefault(0);
            },
            'xf_user_option' => function (Alter $table) {
                $this->addOrChangeColumn($table, 'sv_email_on_mention', 'bool')->setDefault(0);
                $this->addOrChangeColumn($table, 'sv_email_on_quote', 'bool')->setDefault(0);
            },
        ];
    }

    protected function getRemoveAlterTables(): array
    {
        return [
            'xf_user_option' => function (Alter $table) {
                $table->dropColumns(['sv_email_on_mention', 'sv_email_on_quote']);
            },
            'xf_user_group'  => function (Alter $table) {
                $table->dropColumns(['sv_mentionable', 'sv_private', 'sv_avatar_s', 'sv_avatar_l', 'sv_avatar_edit_date']);
            }
        ];
    }
}
