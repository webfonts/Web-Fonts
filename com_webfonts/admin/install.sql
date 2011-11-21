CREATE TABLE IF NOT EXISTS `#__webfonts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `selector` varchar(300) NOT NULL COMMENT 'css selector',
  `fallBack` VARCHAR( 150 ) NOT NULL COMMENT 'Fall back fontstack',
  `vendor` varchar(60) NOT NULL,
  `fontId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `#__webfonts_vendor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `properties` text COMMENT 'vendor specific properties',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

INSERT INTO `#__webfonts_vendor` (`id`, `name`, `properties`) VALUES 
(1, 'Fonts.com', '{"account":{"email":"","firstName":"","lastName":""},"key":"","designers":{},"foundries":{},"classifications":{},"languages":{},"wfspid":""}'),
(2, 'Google Web Fonts', '{"hash":""}');

CREATE TABLE IF NOT EXISTS `#__webfonts_fontscom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ProjectID` varchar(150) NOT NULL,
  `FontID` int(11) NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `family` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `preview` varchar(120) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8_unicode_ci AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `#__webfonts_google` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`kind` VARCHAR( 100 ) NOT NULL ,
`name` VARCHAR( 150 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
`hash` VARCHAR( 32 ) NOT NULL ,
`variant` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
`subset` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL 
) ENGINE = MYISAM ;