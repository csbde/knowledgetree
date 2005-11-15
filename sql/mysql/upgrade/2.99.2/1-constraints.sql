ALTER TABLE `document_type_fieldsets_link` ADD INDEX `document_type_id` (`document_type_id`);
ALTER TABLE `document_type_fieldsets_link` ADD INDEX `fieldset_id` (`fieldset_id`);
ALTER TABLE `document_type_fieldsets_link` ADD CONSTRAINT `document_type_fieldsets_link_ibfk_1` FOREIGN KEY (`document_type_id`) REFERENCES `document_types_lookup` (`id`) ON DELETE CASCADE;
ALTER TABLE `document_type_fieldsets_link` ADD CONSTRAINT `document_type_fieldsets_link_ibfk_2` FOREIGN KEY (`fieldset_id`) REFERENCES `fieldsets` (`id`) ON DELETE CASCADE;
ALTER TABLE `field_orders` ADD CONSTRAINT `field_orders_ibfk_1` FOREIGN KEY (`parent_field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE;
ALTER TABLE `field_orders` ADD CONSTRAINT `field_orders_ibfk_2` FOREIGN KEY (`child_field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE;
ALTER TABLE `field_orders` ADD CONSTRAINT `field_orders_ibfk_3` FOREIGN KEY (`fieldset_id`) REFERENCES `fieldsets` (`id`) ON DELETE CASCADE;
ALTER TABLE `field_value_instances` ADD CONSTRAINT `field_value_instances_ibfk_2` FOREIGN KEY (`field_value_id`) REFERENCES `metadata_lookup` (`id`) ON DELETE CASCADE;
ALTER TABLE `field_value_instances` ADD CONSTRAINT `field_value_instances_ibfk_3` FOREIGN KEY (`behaviour_id`) REFERENCES `field_behaviours` (`id`) ON DELETE CASCADE;
ALTER TABLE `field_value_instances` ADD CONSTRAINT `field_value_instances_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE;
ALTER TABLE `fieldsets` ADD INDEX `master_field` (`master_field`);
ALTER TABLE `fieldsets` ADD CONSTRAINT `fieldsets_ibfk_1` FOREIGN KEY (`master_field`) REFERENCES `document_fields` (`id`) ON DELETE SET NULL;
ALTER TABLE `permission_assignments` ADD INDEX `permission_descriptor_id` (`permission_descriptor_id`);
ALTER TABLE `permission_assignments` ADD CONSTRAINT `permission_assignments_ibfk_3` FOREIGN KEY (`permission_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE;
ALTER TABLE `permission_assignments` ADD CONSTRAINT `permission_assignments_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;
ALTER TABLE `permission_assignments` ADD CONSTRAINT `permission_assignments_ibfk_2` FOREIGN KEY (`permission_object_id`) REFERENCES `permission_objects` (`id`) ON DELETE CASCADE;
ALTER TABLE `permission_descriptor_groups` ADD CONSTRAINT `permission_descriptor_groups_ibfk_1` FOREIGN KEY (`descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE;
ALTER TABLE `permission_descriptor_groups` ADD CONSTRAINT `permission_descriptor_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE;
ALTER TABLE `permission_lookup_assignments` ADD INDEX `permission_descriptor_id` (`permission_descriptor_id`);
ALTER TABLE `permission_lookup_assignments` ADD CONSTRAINT `permission_lookup_assignments_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;
ALTER TABLE `permission_lookup_assignments` ADD CONSTRAINT `permission_lookup_assignments_ibfk_3` FOREIGN KEY (`permission_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE;
ALTER TABLE `permission_lookup_assignments` ADD CONSTRAINT `permission_lookup_assignments_ibfk_2` FOREIGN KEY (`permission_lookup_id`) REFERENCES `permission_lookups` (`id`) ON DELETE CASCADE;
ALTER TABLE `workflow_states` ADD INDEX `inform_descriptor_id` (`inform_descriptor_id`);
ALTER TABLE `workflow_states` ADD CONSTRAINT `workflow_states_ibfk_2` FOREIGN KEY (`inform_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE SET NULL;
ALTER TABLE `workflow_transitions` ADD INDEX `guard_group_id` (`guard_group_id`);
ALTER TABLE `workflow_transitions` ADD INDEX `guard_role_id` (`guard_role_id`);
ALTER TABLE `workflow_transitions` DROP FOREIGN KEY `workflow_transitions_ibfk_2`; # was FOREIGN KEY (`target_state_id`) REFERENCES `workflow_states` (`id`)
ALTER TABLE `workflow_transitions` DROP FOREIGN KEY `workflow_transitions_ibfk_3`; # was FOREIGN KEY (`guard_permission_id`) REFERENCES `permissions` (`id`)
ALTER TABLE `workflow_transitions` DROP FOREIGN KEY `workflow_transitions_ibfk_1`; # was FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`)
ALTER TABLE `field_behaviours` DROP FOREIGN KEY `field_behaviours_ibfk_1`;
ALTER TABLE `field_behaviours` ADD CONSTRAINT `field_behaviours_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE;
ALTER TABLE `workflow_transitions` ADD CONSTRAINT `workflow_transitions_ibfk_48` FOREIGN KEY (`guard_group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE SET NULL;
ALTER TABLE `workflow_transitions` ADD CONSTRAINT `workflow_transitions_ibfk_50` FOREIGN KEY (`guard_condition_id`) REFERENCES `saved_searches` (`id`) ON DELETE SET NULL;
ALTER TABLE `workflow_transitions` ADD CONSTRAINT `workflow_transitions_ibfk_46` FOREIGN KEY (`target_state_id`) REFERENCES `workflow_states` (`id`) ON DELETE CASCADE;
ALTER TABLE `workflow_transitions` ADD CONSTRAINT `workflow_transitions_ibfk_47` FOREIGN KEY (`guard_permission_id`) REFERENCES `permissions` (`id`) ON DELETE SET NULL;
ALTER TABLE `workflow_transitions` ADD CONSTRAINT `workflow_transitions_ibfk_49` FOREIGN KEY (`guard_role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;
ALTER TABLE `workflow_transitions` ADD CONSTRAINT `workflow_transitions_ibfk_45` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`) ON DELETE CASCADE;
