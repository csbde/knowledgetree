ALTER TABLE `workflow_state_permission_assignments`
  DROP FOREIGN KEY `workflow_state_permission_assignments_ibfk_7`,
  DROP FOREIGN KEY `workflow_state_permission_assignments_ibfk_8`;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `workflow_state_permission_assignments` as wspa USING `workflow_state_permission_assignments` as wspa, permissions
	WHERE not exists(select 1 from `permissions` as p where wspa.permission_id = p.id);        

DELETE FROM `workflow_state_permission_assignments` as wspa USING `workflow_state_permission_assignments` as wspa, permission_descriptors
	WHERE not exists(select 1 from `permission_descriptors` as pd where wspa.permission_descriptor_id = pd.id);        

ALTER TABLE `workflow_state_permission_assignments`
  ADD CONSTRAINT `workflow_state_permission_assignments_ibfk_7` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `workflow_state_permission_assignments_ibfk_8` FOREIGN KEY (`permission_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE;
