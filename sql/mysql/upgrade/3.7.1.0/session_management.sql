-- 
-- Add back session management
-- 
UPDATE config_settings SET can_edit="1" WHERE item="allowAutoSignup";
UPDATE config_settings SET can_edit="1" WHERE item="allowAnonymousLogin";