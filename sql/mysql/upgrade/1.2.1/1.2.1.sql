ALTER TABLE folders ADD COLUMN inherit_parent_folder_permission BIT;
UPDATE folders SET inherit_parent_folder_permission = 1;