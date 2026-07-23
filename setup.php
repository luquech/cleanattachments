<?php
define('PLUGIN_CLEANATTACHMENTS_VERSION', '1.3.3');

function plugin_init_cleanattachments() {
    global $PLUGIN_HOOKS;
    $PLUGIN_HOOKS['csrf_compliant']['cleanattachments'] = true;
    Plugin::registerClass('PluginCleanattachmentsCroncleanattachments', [
        'addtabon' => ['CronTask']
    ]);
    Plugin::registerClass('PluginCleanattachmentsConfig', [
        'addtabon' => []
    ]);
    $PLUGIN_HOOKS['config_page']['cleanattachments'] = 'front/config.php';
}

function plugin_version_cleanattachments() {
    return [
        'name'           => 'Clean Attachments',
        'version'        => PLUGIN_CLEANATTACHMENTS_VERSION,
        'author'         => 'Anderson Lucas',
        'license'        => 'GPLv2+',
        'homepage'       => '',
        'minGlpiVersion' => '11.0',
        'requirements'   => ['glpi' => ['min' => '11.0']]
    ];
}

function plugin_cleanattachments_uninstall() {
    global $DB;

    // Remove a tarefa automática registrada
    CronTask::unregister('PluginCleanattachmentsCroncleanattachments');

    // Remove a tabela via arquivo SQL
    $sqlFile = __DIR__ . '/install/uninstall.sql';
    if (file_exists($sqlFile)) {
        $DB->runFile($sqlFile);
    }
    return true;
}
