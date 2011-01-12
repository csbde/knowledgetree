-- 
-- Add timezone support
-- 

UPDATE config_groups SET category="System Settings" WHERE category="General Settings";
UPDATE config_groups SET category="General Settings" WHERE name="tweaks";
UPDATE config_groups SET category="General Settings" WHERE name="session";

ALTER TABLE  `config_settings` CHANGE  `type`  `type` ENUM(  'boolean',  'string',  'numeric_string',  'numeric',  'radio',  'dropdown',  'class' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT  'string';

INSERT INTO `config_settings` (`group_name`, `display_name`, `description`, `item`, `value`, `default_value`, `type`, `options`, `can_edit`) VALUES
('tweaks', 'Set Timezone', 'Defines your timezone you are in.', 'setTimezone', 'default', 'UTC', 'class', 'a:2:{s:4:"file";s:29:"lib/datetime/datetimeutil.inc.php";s:5:"class";s:12:"datetimeutil";}', 1);