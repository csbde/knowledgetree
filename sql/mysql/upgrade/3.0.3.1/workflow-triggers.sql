CREATE TABLE workflow_trigger_instances (
  id int(10) unsigned NOT NULL default '0',
  workflow_transition_id int(11) NOT NULL default '0',
  namespace char(255) NOT NULL default '',
  config_array text,
  PRIMARY KEY  (id),
  KEY workflow_transition_id (workflow_transition_id),
  KEY namespace (namespace)
) TYPE=InnoDB;

CREATE TABLE zseq_workflow_trigger_instances (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
