SELECT @permid:=id FROM `zseq_permissions`;
INSERT INTO `permissions` VALUES (@permid+1, 'ktcore.permissions.folder_rename', 'Rename Folder', 1);

TRUNCATE `zseq_permissions`;
INSERT INTO `zseq_permissions` SELECT MAX(`id`) FROM `permissions`;

SELECT @assignid:=id FROM `zseq_permission_assignments`;

select @permoid:=pa.permission_object_id,@permdid:=pa.permission_descriptor_id from folders f inner join permission_assignments pa on f.permission_object_id=pa.permission_object_id where f.id=1 and f.permission_object_id=pa.permission_object_id limit 1;

INSERT INTO `permission_assignments` VALUES (@assignid+1, @permid+1, @permoid, @permdid);

TRUNCATE `zseq_permission_assignments`;
INSERT INTO `zseq_permission_assignments` SELECT MAX(`id`) FROM `permission_assignments`;
