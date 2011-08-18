CREATE TABLE IF NOT EXISTS `graphicalanalysis_scoring` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namespace` varchar(255) NOT NULL,
  `score` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `namespace` (`namespace`),
  KEY `score` (`score`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;


INSERT INTO `graphicalanalysis_scoring` (`id`, `namespace`, `score`) VALUES
(1, 'ktcore.transactions.create', 10),
(2, 'ktcore.transactions.view', 1),
(3, 'ktcore.transactions.check_out', 2),
(4, 'ktcore.transactions.check_in', 6),
(5, 'ktcore.transactions.download', 2);