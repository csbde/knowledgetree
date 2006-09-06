CREATE TABLE `workflow_state_disabled_actions` (
  `state_id` int(11) NOT NULL default '0',
  `action_name` char(255) NOT NULL default '0',
  KEY `state_id` (`state_id`),
  KEY `action_name` (`action_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `zseq_workflow_state_disabled_actions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
