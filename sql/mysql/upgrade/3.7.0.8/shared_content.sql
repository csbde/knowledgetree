CREATE TABLE IF NOT EXISTS `shared_content` (
  `user_id` int(11) NOT NULL,
  `object_id` int(11) NOT NULL,
  `type` enum('folder','document') NOT NULL DEFAULT 'document',
  `permissions` int(1) NOT NULL DEFAULT '0',
  `parent_id` int(11) DEFAULT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;