CREATE TABLE notifications (
  id int(11) NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  label varchar(255) NOT NULL default "",
  type varchar(255) NOT NULL default "",
  creation_date DATETIME NOT NULL,
  data_int_1 int(11) NOT NULL default '0',
  data_int_2 int(11) NOT NULL default '0',
  data_str_1 VARCHAR(255) NOT NULL default "",
  data_str_2 VARCHAR(255) NOT NULL default "",
  UNIQUE KEY id (id),
  INDEX (type),
  INDEX (user_id)
) TYPE=InnoDB;
CREATE TABLE zseq_notifications (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
