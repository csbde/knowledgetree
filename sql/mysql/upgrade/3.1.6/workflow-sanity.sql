ALTER TABLE `workflows` ADD `enabled` INT(1) UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE `workflow_states` ADD `manage_permissions` INT(1) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `workflow_states` ADD `manage_actions` INT(1) UNSIGNED NOT NULL DEFAULT 0;
