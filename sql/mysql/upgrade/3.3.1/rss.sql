CREATE TABLE `plugin_rss` 
( `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `url` varchar(200) NOT NULL,
  `title` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)) 
ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `zseq_plugin_rss` 
(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,  
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1;

INSERT INTO `zseq_plugin_rss` (id) VALUES ('1');