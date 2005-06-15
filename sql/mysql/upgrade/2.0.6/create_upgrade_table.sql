CREATE TABLE upgrades (
  id int(10) unsigned NOT NULL default '0',
  descriptor char(100) NOT NULL default '',
  description char(255) NOT NULL default '',
  date_performed datetime NOT NULL default '0000-00-00 00:00:00',
  result tinyint(4) NOT NULL default '0',
  parent char(40) default NULL,
  PRIMARY KEY  (id),
  KEY descriptor (descriptor),
  KEY parent (parent)
) TYPE=InnoDB;
CREATE TABLE zseq_upgrades (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;
