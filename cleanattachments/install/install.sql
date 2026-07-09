CREATE TABLE IF NOT EXISTS `glpi_plugin_cleanattachments_config` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `entities_id` int(11) NOT NULL DEFAULT '0',
    `itilcategories_id` int(11) NOT NULL DEFAULT '0',
    `ticket_status` int(11) NOT NULL DEFAULT '5',
    `interval_days` int(11) NOT NULL DEFAULT '60',
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `entities_id` (`entities_id`),
    KEY `itilcategories_id` (`itilcategories_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
