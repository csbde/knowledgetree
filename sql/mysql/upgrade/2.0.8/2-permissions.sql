ALTER TABLE `folders` DROP COLUMN `inherit_parent_folder_permission`;
ALTER TABLE `folders` DROP INDEX `permission_folder_id`;
ALTER TABLE `folders` DROP COLUMN `permission_folder_id`;
