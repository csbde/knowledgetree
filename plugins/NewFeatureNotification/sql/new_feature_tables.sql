-- --------------------------------------------------------

--
-- Table structure for table `new_features_areas`
--

CREATE TABLE IF NOT EXISTS `new_features_areas` (
  `id` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Name of a dispatcher section',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `new_features_areas`
--

INSERT INTO `new_features_areas` (`id`, `name`) VALUES
(3, 'dashboard'),
(4, 'settings'),
(5, 'browse'),
(6, 'view_details');

-- --------------------------------------------------------

--
-- Table structure for table `new_features_messages`
--

CREATE TABLE IF NOT EXISTS `new_features_messages` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `message` varchar(255) NOT NULL COMMENT 'Notification message',
  `div` varchar(255) NOT NULL COMMENT 'The div that the massage should point at',
  `area_id` int(2) unsigned NOT NULL,
  `status` int(1) unsigned NOT NULL COMMENT 'Status of message',
  PRIMARY KEY (`id`),
  KEY `area_id` (`area_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `new_features_messages`
--

INSERT INTO `new_features_messages` (`id`, `message`, `div`, `area_id`, `status`) VALUES
(1, 'New feature dashboard 1', 'new_feture_dashboard_1', 3, 0),
(2, 'New feature settings 2', 'new_feture_settings_2', 4, 0),
(3, 'New feature browse 3', 'new_feture_browse_3', 5, 0),
(4, 'New feature view_details 4', 'new_feture_view_details_4', 6, 0);

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `new_features_users`
--


--
-- Constraints for dumped tables
--

--
-- Constraints for table `new_features_messages`
--
ALTER TABLE `new_features_messages`
  ADD CONSTRAINT `new_features_messages_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `new_features_areas` (`id`);

--
-- Constraints for table `new_features_users`
--
ALTER TABLE `new_features_users`
  ADD CONSTRAINT `new_features_users_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `new_features_messages` (`id`);
