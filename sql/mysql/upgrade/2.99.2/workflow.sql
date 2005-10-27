ALTER TABLE `workflow_states` ADD COLUMN `inform_descriptor_id` int(11) default NULL;
ALTER TABLE `workflow_transitions` CHANGE COLUMN `guard_permission_id` `guard_permission_id` int(11) default '0'; # was int(11) NOT NULL default '0'
ALTER TABLE `workflow_transitions` ADD COLUMN `guard_group_id` int(11) default '0';
ALTER TABLE `workflow_transitions` ADD COLUMN `guard_role_id` int(11) default '0';
