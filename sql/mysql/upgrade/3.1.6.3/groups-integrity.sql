-- PREPARE FOR ADDING CONSTRAINTS ON `groups_groups_link`

-- parent_group_id

DELETE FROM `groups_groups_link` as gg USING `groups_groups_link` as gg, groups_lookup  
	WHERE not exists(select 1 from `groups_lookup` as g where gg.parent_group_id = g.id);

-- member_group_id

DELETE FROM `groups_groups_link` as gg USING `groups_groups_link` as gg, groups_lookup  
	WHERE not exists(select 1 from `groups_lookup` as g where gg.member_group_id = g.id);

-- ADD CONSTRAINT

ALTER TABLE `groups_groups_link`
  ADD CONSTRAINT `groups_groups_link_ibfk_1` FOREIGN KEY (`parent_group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `groups_groups_link_ibfk_2` FOREIGN KEY (`member_group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE;

-- PREPARE FOR ADDING CONSTRAINTS ON `users_groups_link`

-- group_id

DELETE FROM `users_groups_link` as ug USING `users_groups_link` as ug, groups_lookup
	WHERE not exists(select 1 from `groups_lookup` as g where ug.group_id = g.id);

-- user_id

DELETE FROM `users_groups_link` as ug USING `users_groups_link` as ug, users
	WHERE not exists(select 1 from `users` as u where ug.user_id = u.id);

-- ADD CONSTRAINT

ALTER TABLE `users_groups_link`
  ADD CONSTRAINT `users_groups_link_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_groups_link_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;  

