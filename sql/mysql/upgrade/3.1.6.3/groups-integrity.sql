-- PREPARE FOR ADDING CONSTRAINTS ON `groups_groups_link`

-- parent_group_id

CREATE TEMPORARY TABLE cleanup (
  `id` int(11) NOT NULL auto_increment,
  `parent_group_id` int(11) NOT NULL default '0',
  `member_group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `parent_group_id` (`parent_group_id`),
  KEY `member_group_id` (`member_group_id`)
);
INSERT INTO cleanup (id, parent_group_id, member_group_id)
SELECT * FROM `groups_groups_link` AS gg WHERE ( SELECT 1 FROM `groups_lookup` AS g WHERE gg.parent_group_id = g.id);
SELECT * FROM cleanup;
TRUNCATE groups_groups_link;
INSERT groups_groups_link SELECT * FROM cleanup;
DROP TABLE cleanup;

-- member_group_id

CREATE TEMPORARY TABLE cleanup (
  `id` int(11) NOT NULL auto_increment,
  `parent_group_id` int(11) NOT NULL default '0',
  `member_group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `parent_group_id` (`parent_group_id`),
  KEY `member_group_id` (`member_group_id`)
);
INSERT INTO cleanup (id, parent_group_id, member_group_id)
SELECT * FROM `groups_groups_link` AS gg WHERE ( SELECT 1 FROM `groups_lookup` AS g WHERE gg.member_group_id = g.id);
SELECT * FROM cleanup;
TRUNCATE groups_groups_link;
INSERT groups_groups_link SELECT * FROM cleanup;
DROP TABLE cleanup;

-- ADD CONSTRAINT

ALTER TABLE `groups_groups_link`
  ADD CONSTRAINT `groups_groups_link_ibfk_1` FOREIGN KEY (`parent_group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `groups_groups_link_ibfk_2` FOREIGN KEY (`member_group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE;


-- PREPARE FOR ADDING CONSTRAINTS ON `users_groups_link`

-- group_id
CREATE TEMPORARY TABLE cleanup (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`)
);
INSERT INTO cleanup (id, user_id, group_id)
SELECT * FROM `users_groups_link` AS ug WHERE ( SELECT 1 FROM `groups_lookup` AS g WHERE ug.group_id = g.id);
SELECT * FROM cleanup;
TRUNCATE groups_groups_link;
INSERT groups_groups_link SELECT * FROM cleanup;
DROP TABLE cleanup;

-- user_id
CREATE TEMPORARY TABLE cleanup (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`)
);
INSERT INTO cleanup (id, user_id, group_id)
SELECT * FROM `users_groups_link` AS ug WHERE ( SELECT 1 FROM `groups_lookup` AS g WHERE ug.user_id = g.id);
SELECT * FROM cleanup;
TRUNCATE groups_groups_link;
INSERT groups_groups_link SELECT * FROM cleanup;
DROP TABLE cleanup;

-- ADD CONSTRAINT

ALTER TABLE `users_groups_link`
  ADD CONSTRAINT `users_groups_link_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_groups_link_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

