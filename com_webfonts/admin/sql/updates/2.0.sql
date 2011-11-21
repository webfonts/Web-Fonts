INSERT INTO `#__webfonts_vendor` (`id`, `name`, `properties`) VALUES (2, 'Google Web Fonts', '{"hash":"","updated":""}');

DROP TABLE IF EXISTS `#__webfonts_google`;

CREATE TABLE IF NOT EXISTS `#__webfonts_google` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kind` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `family` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `#__webfonts_google_mutant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_fontId` int(11) NOT NULL,
  `mutant` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(30) NOT NULL,
  `inUse` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;