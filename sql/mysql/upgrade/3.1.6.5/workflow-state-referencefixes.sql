ALTER TABLE `workflow_state_permission_assignments`
  DROP FOREIGN KEY `workflow_state_permission_assignments_ibfk_7`,
  DROP FOREIGN KEY `workflow_state_permission_assignments_ibfk_8`;


ALTER TABLE `workflow_state_permission_assignments`
  ADD CONSTRAINT `workflow_state_permission_assignments_ibfk_7` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `workflow_state_permission_assignments_ibfk_8` FOREIGN KEY (`permission_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE;
