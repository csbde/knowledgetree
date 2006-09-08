ALTER TABLE `groups_groups_link`
  ADD CONSTRAINT `groups_groups_link_ibfk_1` FOREIGN KEY (`parent_group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `groups_groups_link_ibfk_2` FOREIGN KEY (`member_group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE;
  
ALTER TABLE `users_groups_link`
  ADD CONSTRAINT `users_groups_link_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_groups_link_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;  
