ALTER TABLE folders ADD COLUMN inherit_parent_folder_permission BIT;
UPDATE folders SET inherit_parent_folder_permission = 1;
UPDATE system_settings SET value="1.2.1" WHERE name="knowledgeTreeVersion";