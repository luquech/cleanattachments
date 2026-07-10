<?php
function plugin_cleanattachments_install() {
    global $DB;

    // Executa o arquivo SQL de instalação (método seguro, não bloqueado)
    $sqlFile = __DIR__ . '/install/install.sql';
    if (file_exists($sqlFile)) {
        $DB->runFile($sqlFile);
    } else {
        // Fallback (não recomendado, mas mantido para emergência)
        if (method_exists($DB, 'setAllowDirectQuery')) {
            $DB->setAllowDirectQuery(true);
        }
        $DB->query("CREATE TABLE IF NOT EXISTS `glpi_plugin_cleanattachments_config` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `entities_id` int(11) NOT NULL DEFAULT '0',
            `itilcategories_id` int(11) NOT NULL DEFAULT '0',
            `ticket_status` int(11) NOT NULL DEFAULT '5',
            `interval_days` int(11) NOT NULL DEFAULT '60',
            `interval_unit` ENUM('days', 'minutes') NOT NULL DEFAULT 'days',
            `is_active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`id`),
            KEY `entities_id` (`entities_id`),
            KEY `itilcategories_id` (`itilcategories_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    // Registra a tarefa agendada
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
