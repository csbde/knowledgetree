SET FOREIGN_KEY_CHECKS=0;
CREATE TABLE `permission_descriptor_roles` (
  `descriptor_id` int(11) NOT NULL default '0',
  `role_id` int(11) NOT NULL default '0',
  UNIQUE KEY `descriptor_id` (`descriptor_id`,`role_id`),
  KEY `descriptor_id_2` (`descriptor_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `permission_descriptor_roles_ibfk_1` FOREIGN KEY (`descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE,  CONSTRAINT `permission_descriptor_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) TYPE=InnoDB;

CREATE TABLE `permission_descriptor_users` (
  `descriptor_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  UNIQUE KEY `descriptor_id` (`descriptor_id`,`user_id`),
  KEY `descriptor_id_2` (`descriptor_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `permission_descriptor_users_ibfk_1` FOREIGN KEY (`descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE,  CONSTRAINT `permission_descriptor_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) TYPE=InnoDB;
SET FOREIGN_KEY_CHECKS=1;
