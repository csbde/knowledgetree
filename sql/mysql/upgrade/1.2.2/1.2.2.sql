ALTER TABLE discussion_comments ADD COLUMN in_reply_to INTEGER;
UPDATE system_settings SET value="1.2.2" WHERE name="knowledgeTreeVersion";