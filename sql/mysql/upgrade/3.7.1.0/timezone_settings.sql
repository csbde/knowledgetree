-- 
-- Add timezone support
-- 

UPDATE config_groups SET category="System Settings" WHERE category="General Settings";
UPDATE config_groups SET category="General Settings" WHERE name="session";

ALTER TABLE  `config_settings` CHANGE  `type`  `type` ENUM(  'boolean',  'string',  'numeric_string',  'numeric',  'radio',  'dropdown',  'class' ) DEFAULT 'string';

INSERT INTO `config_settings` (`group_name`, `display_name`, `description`, `item`, `value`, `default_value`, `type`, `options`, `can_edit`) VALUES
('timezone', '', '', 'setTimezone', 'default', 'UTC', 'class', 'a:2:{s:5:"class";s:13:"datetime_view";s:4:"file";s:34:"plugins/datetime/datetime_view.php";}', 1);

INSERT INTO `config_groups` (`name`, `display_name`, `description`, `category`) VALUES
('timezone', 'Timezone', 'Timezone configuration settings', 'General Settings');