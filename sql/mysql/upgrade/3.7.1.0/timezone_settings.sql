-- 
-- Add timezone support
-- 

UPDATE config_groups SET category="System Settings" WHERE category="General Settings";
UPDATE config_groups SET category="General Settings" WHERE name="session";

ALTER TABLE  `config_settings` CHANGE  `type`  `type` ENUM(  'boolean',  'string',  'numeric_string',  'numeric',  'radio',  'dropdown',  'class' ) DEFAULT 'string';

INSERT INTO `config_settings` (`group_name`, `display_name`, `description`, `item`, `value`, `default_value`, `type`, `options`, `can_edit`) VALUES
('timezone', 'Set Timezone', '', 'setTimezone', 'default', 'UTC', 'class', 'a:2:{s:4:"file";s:33:"lib/datetime/datetimeutil.inc.php";s:5:"class";s:12:"datetimeutil";}', 1);

INSERT INTO `config_groups` (`name`, `display_name`, `description`, `category`) VALUES
('timezone', 'Timezone', 'Timezone configuration settings', 'General Settings');