CREATE TABLE `mime_documents` (
	`id` int(11) NOT NULL,
	`mime_doc` varchar(100) default NULL,
	`icon_path` varchar(20) default NULL,
	PRIMARY KEY  (`id`)
) ENGINE=innodb DEFAULT CHARSET=utf8;

CREATE TABLE `zseq_mime_documents` (
	`id` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table mime_types add extractor varchar(100);
alter table mime_types add mime_document_id int;
