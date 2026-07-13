<?php
function plugin_cleanattachments_install() {
    global $DB;

    $sqlFile = __DIR__ . '/install/install.sql';
    if (file_exists($sqlFile)) {
        $DB->runFile($sqlFile);
    } else {
        if (method_exists($DB, 'setAllowDirectQuery')) {
            $DB->setAllowDirectQuery(true);
        }
        $DB->query("CREATE TABLE IF NOT EXISTS `glpi_plugin_cleanattachments_config` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `entities_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
            `itilcategories_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
            `ticket_status` INT(11) NOT NULL DEFAULT '5',
            `interval_days` INT(11) NOT NULL DEFAULT '60',
            `interval_unit` ENUM('days', 'minutes') NOT NULL DEFAULT 'days',
            `is_active` TINYINT(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`id`),
            KEY `entities_id` (`entities_id`),
            KEY `itilcategories_id` (`itilcategories_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    CronTask::register(
        'PluginCleanattachmentsCroncleanattachments',
        'cleanAttachments',
        86400,
        [
            'name'   => __('Limpar anexos conforme regras configuradas', 'cleanattachments'),
            'state'  => 0,
            'mode'   => CronTask::MODE_INTERNAL,
        ]
    );
    return true;
}
