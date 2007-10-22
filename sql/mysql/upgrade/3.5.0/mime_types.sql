CREATE TABLE `mime_documents` (
	`id` int(11) NOT NULL,
	`mime_doc` varchar(100) default NULL,
	`icon_path` varchar(20) default NULL,
	PRIMARY KEY  (`id`)
) ENGINE=innodb DEFAULT CHARSET=utf8;

CREATE TABLE `zseq_mime_documents` (
	`id` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `mime_extractors`
(
	`id` mediumint(9) NOT NULL,
    `name` varchar(50) NOT NULL,
    `active` tinyint(4) NOT NULL default '0',
    PRIMARY KEY  (`id`)
) ENGINE=innodb DEFAULT CHARSET=utf8;

CREATE TABLE `zseq_mime_extractors` (
	`id` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `mime_document_mapping`
(
	`mime_document_id` int(11) NOT NULL,
    `mime_type_id` int(11) NOT NULL,
    PRIMARY KEY  (`mime_document_id`,`mime_type_id`)
) ENGINE=innodb DEFAULT CHARSET=utf8;

alter table mime_types add extractor_id mediumint;
alter table mime_types add mime_document_id int;

alter table mime_types add foreign key (extractor_id) references mime_extractors (id);
alter table mime_types add foreign key (mime_document_id) references mime_documents (id);

alter table mime_document_mapping add foreign key (mime_type_id) references mime_types (id);
alter table mime_document_mapping add foreign key (mime_document_id) references mime_documents (id);
