SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `document_role_allocations` (
  `id` int(11) NOT NULL default '0',
  `document_id` int(11) NOT NULL default '0',
  `role_id` int(11) NOT NULL default '0',
  `permission_descriptor_id` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `document_id` (`document_id`)
) TYPE=InnoDB ;

CREATE TABLE `zseq_document_role_allocations` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;

INSERT INTO `roles` VALUES (-1, 'Owner');
ALTER TABLE `documents` ADD `owner_id` int(11) NOT NULL default '0';
UPDATE `documents` SET `owner_id` = `creator_id`;


SET FOREIGN_KEY_CHECKS=1;
