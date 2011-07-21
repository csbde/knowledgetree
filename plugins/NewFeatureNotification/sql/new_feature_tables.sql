-- --------------------------------------------------------

--
-- Table structure for table `new_features_areas`
--

CREATE TABLE IF NOT EXISTS `new_features_areas` (
  `id` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Name of a dispatcher section',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `new_features_areas`
--

INSERT INTO `new_features_areas` (`id`, `name`) VALUES
(1, 'dashboard'),
(2, 'settings'),
(3, 'browse'),
(4, 'view_details');

-- --------------------------------------------------------

--
-- Table structure for table `new_features_messages`
--

CREATE TABLE IF NOT EXISTS `new_features_messages` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `message` varchar(255) NOT NULL COMMENT 'Notification message',
  `div` varchar(255) NOT NULL COMMENT 'The div that the massage should point at',
  `area_id` int(2) unsigned NOT NULL,
  `type` enum('all', 'admin', 'normal') default 'all',
  `status` enum('enabled', 'disabled') default 'enabled',
  PRIMARY KEY (`id`),
  KEY `area_id` (`area_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `new_features_users`
--

CREATE TABLE IF NOT EXISTS `new_features_users` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL COMMENT 'User id',
  `message_id` int(5) unsigned NOT NULL COMMENT 'New features message',
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `new_features_users`
--