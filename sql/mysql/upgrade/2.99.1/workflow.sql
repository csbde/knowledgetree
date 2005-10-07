SET FOREIGN_KEY_CHECKS=0;
CREATE TABLE `workflow_actions` (
  `workflow_id` int(11) NOT NULL default '0',
  `action_name` char(255) NOT NULL default '',
  KEY `workflow_id` (`workflow_id`),
  KEY `action_name` (`action_name`)
) TYPE=InnoDB;

CREATE TABLE `workflow_documents` (
  `document_id` int(11) NOT NULL default '0',
  `workflow_id` int(11) NOT NULL default '0',
  `state_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`document_id`),
  KEY `workflow_id` (`workflow_id`),
  KEY `state_id` (`state_id`)
) TYPE=InnoDB;

CREATE TABLE `workflow_state_actions` (
  `state_id` int(11) NOT NULL default '0',
  `action_name` char(255) NOT NULL default '0',
  KEY `state_id` (`state_id`),
  KEY `action_name` (`action_name`)
) TYPE=InnoDB;

CREATE TABLE `workflow_state_transitions` (
  `state_id` int(11) NOT NULL default '0',
  `transition_id` int(11) NOT NULL default '0'
) TYPE=InnoDB;

CREATE TABLE `workflow_states` (
  `id` int(11) NOT NULL default '0',
  `workflow_id` int(11) NOT NULL default '0',
  `name` char(255) NOT NULL default '',
  `human_name` char(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `workflow_id` (`workflow_id`),
  KEY `name` (`name`),
  CONSTRAINT `workflow_states_ibfk_1` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`)
) TYPE=InnoDB;

CREATE TABLE `workflow_transitions` (
  `id` int(11) NOT NULL default '0',
  `workflow_id` int(11) NOT NULL default '0',
  `name` char(255) NOT NULL default '',
  `human_name` char(100) NOT NULL default '',
  `target_state_id` int(11) NOT NULL default '0',
  `guard_permission_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `workflow_id_2` (`workflow_id`,`name`),
  KEY `workflow_id` (`workflow_id`),
  KEY `name` (`name`),
  KEY `target_state_id` (`target_state_id`),
  KEY `guard_permission_id` (`guard_permission_id`),
  CONSTRAINT `workflow_transitions_ibfk_1` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`),
  CONSTRAINT `workflow_transitions_ibfk_2` FOREIGN KEY (`target_state_id`) REFERENCES `workflow_states` (`id`),
  CONSTRAINT `workflow_transitions_ibfk_3` FOREIGN KEY (`guard_permission_id`) REFERENCES `permissions` (`id`)
) TYPE=InnoDB;

CREATE TABLE `workflows` (
  `id` int(11) NOT NULL default '0',
  `name` char(250) NOT NULL default '',
  `human_name` char(100) NOT NULL default '',
  `start_state_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `start_state_id` (`start_state_id`),
  CONSTRAINT `workflows_ibfk_1` FOREIGN KEY (`start_state_id`) REFERENCES `workflow_states` (`id`)
) TYPE=InnoDB;

CREATE TABLE `zseq_workflow_states` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `zseq_workflow_transitions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `zseq_workflows` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET FOREIGN_KEY_CHECKS=1;
