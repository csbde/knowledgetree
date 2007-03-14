
SELECT @permid:=id FROM `zseq_permissions`;
INSERT INTO `permissions` VALUES (@permid, 'ktcore.permissions.folder_rename', 'Rename Folder', 1);

TRUNCATE `zseq_permissions`;
INSERT INTO `zseq_permissions` SELECT MAX(`id`) FROM `permissions`;

