ALTER TABLE `plugin_helper` CHANGE `viewtype` `viewtype` ENUM( 'general', 'dashboard', 'plugin', 'folder', 'document', 'admindispatcher', 'dispatcher', 'process' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'general';