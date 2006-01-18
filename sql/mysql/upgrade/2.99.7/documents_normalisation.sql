CREATE TABLE `document_content_version` (
  `id` int(11) NOT NULL default '0',
  `document_id` int(11) NOT NULL default '0',
  `filename` text NOT NULL,
  `size` bigint(20) NOT NULL default '0',
  `mime_id` int(11) NOT NULL default '0',
  `major_version` int(11) NOT NULL default '0',
  `minor_version` int(11) NOT NULL default '0',
  `storage_path` varchar(250) default NULL,
  UNIQUE KEY `id` (`id`),
  KEY `storage_path` (`storage_path`),
  KEY `document_id` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `document_metadata_version` (
  `id` int(11) NOT NULL default '0',
  `document_id` int(11) NOT NULL default '0',
  `content_version_id` int(11) NOT NULL default '0',
  `document_type_id` int(11) NOT NULL default '0',
  `name` text NOT NULL,
  `description` varchar(200) NOT NULL default '',
  `status_id` int(11) default NULL,
  `metadata_version` int(11) NOT NULL default '0',
  `version_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `version_creator_id` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `fk_document_type_id` (`document_type_id`),
  KEY `fk_status_id` (`status_id`),
  KEY `document_id` (`document_id`),
  KEY `version_created` (`version_created`),
  KEY `version_creator_id` (`version_creator_id`),
  KEY `content_version_id` (`content_version_id`),
  CONSTRAINT `document_metadata_version_ibfk_4` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_metadata_version_ibfk_5` FOREIGN KEY (`document_type_id`) REFERENCES `document_types_lookup` (`id`),
  CONSTRAINT `document_metadata_version_ibfk_6` FOREIGN KEY (`status_id`) REFERENCES `status_lookup` (`id`),
  CONSTRAINT `document_metadata_version_ibfk_7` FOREIGN KEY (`version_creator_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
