SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE `document_type_fieldsets_link` ADD INDEX `document_type_id` (`document_type_id`);
ALTER TABLE `document_type_fieldsets_link` ADD INDEX `fieldset_id` (`fieldset_id`);

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `document_type_fieldsets_link` as dtfl USING `document_type_fieldsets_link` as dtfl, document_types_lookup
	WHERE not exists(select 1 from `document_types_lookup` as dtl where dtfl.document_type_id = dtl.id);
        
ALTER TABLE `document_type_fieldsets_link` ADD CONSTRAINT `document_type_fieldsets_link_ibfk_1` FOREIGN KEY (`document_type_id`) REFERENCES `document_types_lookup` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `document_type_fieldsets_link` as dtfl USING `document_type_fieldsets_link` as dtfl, fieldsets
	WHERE not exists(select 1 from `fieldsets` as fs where dtfl.fieldset_id = fs.id);
        
ALTER TABLE `document_type_fieldsets_link` ADD CONSTRAINT `document_type_fieldsets_link_ibfk_2` FOREIGN KEY (`fieldset_id`) REFERENCES `fieldsets` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `field_orders` as fo USING `field_orders` as fo, document_fields
	WHERE not exists(select 1 from `document_fields` as df where fo.parent_field_id = df.id);

ALTER TABLE `field_orders` ADD CONSTRAINT `field_orders_ibfk_1` FOREIGN KEY (`parent_field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `field_orders` as fo USING `field_orders` as fo, document_fields
	WHERE not exists(select 1 from `document_fields` as df where fo.child_field_id = df.id);

ALTER TABLE `field_orders` ADD CONSTRAINT `field_orders_ibfk_2` FOREIGN KEY (`child_field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `field_orders` as fo USING `field_orders` as fo, fieldsets
	WHERE not exists(select 1 from `fieldsets` as fs where fo.fieldset_id = fs.id);
        
ALTER TABLE `field_orders` ADD CONSTRAINT `field_orders_ibfk_3` FOREIGN KEY (`fieldset_id`) REFERENCES `fieldsets` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `field_value_instances` as fvi USING `field_value_instances` as fvi, metadata_lookup
	WHERE not exists(select 1 from `metadata_lookup` as ml where fvi.field_value_id = ml.id);

ALTER TABLE `field_value_instances` ADD CONSTRAINT `field_value_instances_ibfk_2` FOREIGN KEY (`field_value_id`) REFERENCES `metadata_lookup` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `field_value_instances` as fvi USING `field_value_instances` as fvi, field_behaviours
	WHERE not exists(select 1 from `field_behaviours` as fb where fvi.behaviour_id = fb.id);

ALTER TABLE `field_value_instances` ADD CONSTRAINT `field_value_instances_ibfk_3` FOREIGN KEY (`behaviour_id`) REFERENCES `field_behaviours` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `field_value_instances` as fvi USING `field_value_instances` as fvi, document_fields
	WHERE not exists(select 1 from `document_fields` as df where fvi.field_id = df.id);

ALTER TABLE `field_value_instances` ADD CONSTRAINT `field_value_instances_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE;


ALTER TABLE `fieldsets` ADD INDEX `master_field` (`master_field`);

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `fieldsets` as fs USING `fieldsets` as fs, document_fields
	WHERE not exists(select 1 from `document_fields` as df where fs.master_field = df.id);

ALTER TABLE `fieldsets` ADD CONSTRAINT `fieldsets_ibfk_1` FOREIGN KEY (`master_field`) REFERENCES `document_fields` (`id`) ON DELETE SET NULL;

ALTER TABLE `permission_assignments` ADD INDEX `permission_descriptor_id` (`permission_descriptor_id`);

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `permission_assignments` as pa USING `permission_assignments` as pa, permission_descriptors
	WHERE not exists(select 1 from `permission_descriptors` as pd where pa.permission_descriptor_id = pd.id);

ALTER TABLE `permission_assignments` ADD CONSTRAINT `permission_assignments_ibfk_3` FOREIGN KEY (`permission_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `permission_assignments` as pa USING `permission_assignments` as pa, permissions
	WHERE not exists(select 1 from `permissions` as p where pa.permission_id =p.id);

ALTER TABLE `permission_assignments` ADD CONSTRAINT `permission_assignments_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `permission_assignments` as pa USING `permission_assignments` as pa, permission_objects
	WHERE not exists(select 1 from `permission_objects` as po where pa.permission_object_id =po.id);
        
ALTER TABLE `permission_assignments` ADD CONSTRAINT `permission_assignments_ibfk_2` FOREIGN KEY (`permission_object_id`) REFERENCES `permission_objects` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `permission_descriptor_groups` as pdg USING `permission_descriptor_groups` as pdg, permission_descriptors
	WHERE not exists(select 1 from `permission_descriptors` as pd where pdg.descriptor_id =pd.id);

ALTER TABLE `permission_descriptor_groups` ADD CONSTRAINT `permission_descriptor_groups_ibfk_1` FOREIGN KEY (`descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `permission_descriptor_groups` as pdg USING `permission_descriptor_groups` as pdg, groups_lookup
	WHERE not exists(select 1 from `groups_lookup` as gl where pdg.group_id = gl.id);

ALTER TABLE `permission_descriptor_groups` ADD CONSTRAINT `permission_descriptor_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE;

ALTER TABLE `permission_lookup_assignments` ADD INDEX `permission_descriptor_id` (`permission_descriptor_id`);

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `permission_lookup_assignments` as pla USING `permission_lookup_assignments` as pla, permissions
	WHERE not exists(select 1 from `permissions` as p where pla.permission_id = p.id);

ALTER TABLE `permission_lookup_assignments` ADD CONSTRAINT `permission_lookup_assignments_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `permission_lookup_assignments` as pla USING `permission_lookup_assignments` as pla, permission_descriptors
	WHERE not exists(select 1 from `permission_descriptors` as pd where pla.permission_descriptor_id = pd.id);

ALTER TABLE `permission_lookup_assignments` ADD CONSTRAINT `permission_lookup_assignments_ibfk_3` FOREIGN KEY (`permission_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `permission_lookup_assignments` as pla USING `permission_lookup_assignments` as pla, permission_lookups
	WHERE not exists(select 1 from `permission_lookups` as pl where pla.permission_lookup_id = pl.id);

ALTER TABLE `permission_lookup_assignments` ADD CONSTRAINT `permission_lookup_assignments_ibfk_2` FOREIGN KEY (`permission_lookup_id`) REFERENCES `permission_lookups` (`id`) ON DELETE CASCADE;

ALTER TABLE `workflow_states` ADD INDEX `inform_descriptor_id` (`inform_descriptor_id`);


-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

UPDATE workflow_states
    SET inform_descriptor_id = null 
    WHERE not exists(select 1 from `permission_descriptors` as pd where workflow_states.inform_descriptor_id =pd.id);

ALTER TABLE `workflow_states` ADD CONSTRAINT `workflow_states_ibfk_2` FOREIGN KEY (`inform_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE SET NULL;

ALTER TABLE `workflow_transitions` ADD INDEX `guard_group_id` (`guard_group_id`);
ALTER TABLE `workflow_transitions` ADD INDEX `guard_role_id` (`guard_role_id`);

ALTER TABLE `workflow_transitions` DROP FOREIGN KEY `workflow_transitions_ibfk_2`; # was FOREIGN KEY (`target_state_id`) REFERENCES `workflow_states` (`id`)
ALTER TABLE `workflow_transitions` DROP FOREIGN KEY `workflow_transitions_ibfk_3`; # was FOREIGN KEY (`guard_permission_id`) REFERENCES `permissions` (`id`)
ALTER TABLE `workflow_transitions` DROP FOREIGN KEY `workflow_transitions_ibfk_1`; # was FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`)
ALTER TABLE `field_behaviours` DROP FOREIGN KEY `field_behaviours_ibfk_1`;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `field_behaviours` as fb USING `field_behaviours` as fb, document_fields
	WHERE not exists(select 1 from `document_fields` as df where fb.field_id = df.id);

ALTER TABLE `field_behaviours` ADD CONSTRAINT `field_behaviours_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

UPDATE workflow_transitions
    SET guard_group_id = null 
    WHERE not exists(select 1 from `groups_lookup` as gl where workflow_transitions.guard_group_id =gl.id);

ALTER TABLE `workflow_transitions` ADD CONSTRAINT `workflow_transitions_ibfk_48` FOREIGN KEY (`guard_group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE SET NULL;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

UPDATE workflow_transitions
    SET guard_condition_id = null 
    WHERE not exists(select 1 from `saved_searches` as ss where workflow_transitions.guard_condition_id =ss.id);

ALTER TABLE `workflow_transitions` ADD CONSTRAINT `workflow_transitions_ibfk_50` FOREIGN KEY (`guard_condition_id`) REFERENCES `saved_searches` (`id`) ON DELETE SET NULL;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `workflow_transitions` as wt USING `workflow_transitions` as wt, workflow_states
	WHERE not exists(select 1 from `workflow_states` as ws where wt.target_state_id = ws.id);

ALTER TABLE `workflow_transitions` ADD CONSTRAINT `workflow_transitions_ibfk_46` FOREIGN KEY (`target_state_id`) REFERENCES `workflow_states` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

UPDATE workflow_transitions
    SET guard_permission_id = null 
    WHERE not exists(select 1 from `permissions` as p where workflow_transitions.guard_permission_id =p.id);

ALTER TABLE `workflow_transitions` ADD CONSTRAINT `workflow_transitions_ibfk_47` FOREIGN KEY (`guard_permission_id`) REFERENCES `permissions` (`id`) ON DELETE SET NULL;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

UPDATE workflow_transitions
    SET guard_role_id = null 
    WHERE not exists(select 1 from `roles` as r where workflow_transitions.guard_role_id =r.id);

ALTER TABLE `workflow_transitions` ADD CONSTRAINT `workflow_transitions_ibfk_49` FOREIGN KEY (`guard_role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `workflow_transitions` as wt USING `workflow_transitions` as wt, workflows
	WHERE not exists(select 1 from `workflows` as w where wt.workflow_id = w.id);
        
ALTER TABLE `workflow_transitions` ADD CONSTRAINT `workflow_transitions_ibfk_45` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS=1;
