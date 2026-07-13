CREATE TABLE IF NOT EXISTS `glpi_plugin_cleanattachments_rules` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `entities_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `itilcategories_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `ticket_status` INT(11) NOT NULL DEFAULT '5',
    `interval_days` INT(11) NOT NULL DEFAULT '60',
    `interval_unit` ENUM('days','minutes') NOT NULL DEFAULT 'days',
    `is_active` TINYINT(1) NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `entities_id` (`entities_id`),
    KEY `itilcategories_id` (`itilcategories_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
