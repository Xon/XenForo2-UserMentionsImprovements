<?php

namespace SV\UserMentionsImprovements;

use SV\StandardLib\InstallerHelper;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Entity\User;

class Setup extends AbstractSetup
{
    use InstallerHelper;
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1()
    {
        $this->schemaManager()->alterTable(
            'xf_user_group', function (Alter $table) {
            $this->addOrChangeColumn($table, 'sv_mentionable', 'bool')->setDefault(0);
            $this->addOrChangeColumn($table, 'sv_private', 'bool')->setDefault(0);
            $this->addOrChangeColumn($table, 'sv_avatar_s', 'text')->nullable()->setDefault(null);
            $this->addOrChangeColumn($table, 'sv_avatar_l', 'text')->nullable()->setDefault(null);
            $this->addOrChangeColumn($table, 'sv_avatar_edit_date', 'int')->setDefault(0);
        }
        );
        $this->db()->query(
            "
                UPDATE xf_user_group
                SET sv_avatar_edit_date = ?
                WHERE sv_avatar_edit_date = 0
            ", [\XF::$time]
        );

        $this->schemaManager()->alterTable(
            'xf_user_option', function (Alter $table) {
            $this->addOrChangeColumn($table, 'sv_email_on_mention', 'bool')->setDefault(0);
            $this->addOrChangeColumn($table, 'sv_email_on_quote', 'bool')->setDefault(0);
        }
        );

        $this->applyRegistrationDefaults([
            'sv_email_on_mention' => 0,
            'sv_email_on_quote'   => 0,
        ]);
    }

    public function installStep2()
    {
        $this->defaultPermission();
    }

    public function upgrade1010000Step1()
    {
        /** @noinspection SqlResolve */
        $this->db()->query(
            "
                UPDATE xf_user_group
                SET last_edit_date = ?
                WHERE last_edit_date = 0
            ", [\XF::$time]
        );
    }

    public function upgrade104010Step1()
    {
        /** @noinspection SqlResolve */
        /** @noinspection SqlWithoutWhere */
        $this->db()->query(
            "
                UPDATE xf_user_option
                SET sv_email_on_quote = sv_email_on_tag
            "
        );
    }

    public function upgrade1000900Step1()
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
    public function upgrade2000070Step1()
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

        /** @var \XF\Entity\Option $entity */
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

    public function upgrade2000070Step2()
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
            'XF:PermissionRebuild',
            [],
            false
        );
    }

    /**
     * Rewrite options
     */
    public function upgrade2000070Step3()
    {
        /** @var \XF\Entity\Option[] $options */
        $options = $this->app->finder('XF:Option')->whereIds(['sv_default_group_avatar_s', 'sv_default_group_avatar_l'])->fetch();
        foreach ($options as $option)
        {
            $value = $option->getOptionValue();
            if (is_string($value))
            {
                $option->option_value = str_replace('styles/default/sv/tagging/', 'styles/default/sv/mentionimprovements/', $value);
                $option->saveIfChanged();
            }
        }
    }

    public function upgrade2020000Step1()
    {
        $this->applyGlobalPermissionByGroup('general', 'sv_ViewPublicGroups', [User::GROUP_REG, User::GROUP_GUEST]);
    }

    public function upgrade2030400Step1()
    {
        $this->installStep1();
    }

    public function upgrade2040000Step1()
    {
        $this->app->jobManager()->enqueueUnique('permissionRebuild', 'XF:PermissionRebuild', [], true);
    }

    public function uninstallStep1()
    {
        $this->schemaManager()->alterTable(
            'xf_user_group', function (Alter $table) {
            $table->dropColumns(['sv_mentionable', 'sv_private', 'sv_avatar_s', 'sv_avatar_l', 'sv_avatar_edit_date']);
        }
        );
    }

    public function uninstallStep2()
    {
        $this->schemaManager()->alterTable(
            'xf_user_option', function (Alter $table) {
            $table->dropColumns(['sv_email_on_mention', 'sv_email_on_quote']);
        }
        );
    }

    public function uninstallStep3()
    {
        $db = $this->db();

        $db->query(
            "
            DELETE FROM xf_permission_entry
            WHERE permission_id IN (            
                 'sv_EnableMentions',
                 'sv_MentionUserGroup',
                 'sv_ReceiveMentionEmails',
                 'sv_ReceiveQuoteEmails',
                 'sv_ViewPrivateGroups'
            )
        "
        );

        $db->query(
            "
            DELETE FROM xf_permission_entry_content
            WHERE permission_id IN (
                 'sv_EnableMentions',
                 'sv_MentionUserGroup',
                 'sv_ReceiveMentionEmails',
                 'sv_ReceiveQuoteEmails',
                 'sv_ViewPrivateGroups'
            )
        "
        );
    }

    public function defaultPermission()
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

        $db->query(
            "INSERT IGNORE INTO xf_permission_entry (user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
                SELECT DISTINCT user_group_id, user_id, 'forum', 'sv_ReceiveQuoteEmails', 'allow', 0
                FROM xf_permission_entry
                WHERE permission_group_id = 'general' AND permission_id IN ('maxMentionedUsers') AND permission_value_int <> 0
            "
        );
        $db->query(
            "INSERT IGNORE INTO xf_permission_entry_content (content_type, content_id, user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
                SELECT DISTINCT content_type, content_id, user_group_id, user_id, 'forum', 'sv_ReceiveQuoteEmails', 'content_allow', 0
                FROM xf_permission_entry_content
                WHERE permission_group_id = 'general' AND permission_id IN ('maxMentionedUsers') AND permission_value_int <> 0
            "
        );

        $db->query(
            "INSERT IGNORE INTO xf_permission_entry (user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
                SELECT DISTINCT user_group_id, user_id, 'forum', 'sv_ReceiveMentionEmails', 'allow', 0
                FROM xf_permission_entry
                WHERE permission_group_id = 'general' AND permission_id IN ('maxMentionedUsers') AND permission_value_int <> 0
            "
        );
        $db->query(
            "INSERT IGNORE INTO xf_permission_entry_content (content_type, content_id, user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
                SELECT DISTINCT content_type, content_id, user_group_id, user_id, 'forum', 'sv_ReceiveMentionEmails', 'content_allow', 0
                FROM xf_permission_entry_content
                WHERE permission_group_id = 'general' AND permission_id IN ('maxMentionedUsers') AND permission_value_int <> 0
            "
        );
    }
}
