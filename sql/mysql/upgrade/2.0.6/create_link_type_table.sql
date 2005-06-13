ALTER TABLE document_link ADD link_type_id INT DEFAULT '0' NOT NULL;
CREATE TABLE document_link_types (
  id int(11) NOT NULL default '0',
  name char(100) NOT NULL default '',
  description char(255) NOT NULL default '',
  UNIQUE KEY id (id)
) TYPE=InnoDB;
CREATE TABLE zseq_document_link_types (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;
INSERT INTO document_link_types VALUES (0, 'Default', 'Default link type');
INSERT INTO zseq_document_link_types VALUES (2);
