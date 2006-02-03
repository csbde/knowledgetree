CREATE TABLE `workflow_state_permission_assignments` (
  `id` int(11) NOT NULL,
  `workflow_state_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `permission_descriptor_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `permission_id` (`permission_id`),
  KEY `permission_descriptor_id` (`permission_descriptor_id`),
  KEY `workflow_state_id` (`workflow_state_id`),
  CONSTRAINT `workflow_state_permission_assignments_ibfk_7` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`),
  CONSTRAINT `workflow_state_permission_assignments_ibfk_8` FOREIGN KEY (`permission_descriptor_id`) REFERENCES `permission_descriptors` (`id`)
) TYPE=InnoDB;

CREATE TABLE `zseq_workflow_state_permission_assignments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;
