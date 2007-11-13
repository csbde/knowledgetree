CREATE TABLE `search_ranking` (
	`groupname` varchar(100) NOT NULL,
	`itemname` varchar(100) NOT NULL,
	`ranking` float default '0',
	`type` enum('T','M','S') default 'T' COMMENT 'T=Table, M=Metadata, S=Searchable',
	PRIMARY KEY  (`groupname`,`itemname`)
) ENGINE=innodb DEFAULT CHARSET=utf8;


INSERT INTO `search_ranking` VALUES
	('documents','checked_out_user_id',1,'T'),
	('documents','creator_id',1,'T'),
	('documents','created',1,'T'),
	('documents','id',1,'T'),
	('document_metadata_version','document_type_id',1,'T'),
	('document_content_version','filename',10,'T'),
	('document_content_version','filesize',1,'T'),
	('documents','is_checked_out',1,'T'),
	('documents','immutable',1,'T'),
	('documents','modified_user_id',1,'T'),
	('documents','modified',1,'T'),
	('tag_words','tag',1,'T'),
	('document_metadata_version','name',1,'T'),
	('document_metadata_version','workflow_id',1,'T'),
	('document_metadata_version','workflow_state_id',1,'T'),
	('Discussion','',150,'S'),
	('DocumentText','',100,'S'),
	('documents','title',300,'T');

