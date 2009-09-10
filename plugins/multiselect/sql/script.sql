ALTER TABLE `document_fields`
	ADD `has_inetlookup` tinyint(1) default NULL;

ALTER TABLE `document_fields`
	ADD `inetlookup_type` varchar(255) default NULL;