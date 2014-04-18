--
-- Table structure for table `civicrm_autorelationship_contact_city`
--

CREATE TABLE IF NOT EXISTS `civicrm_autorelationship_contact_gemeente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL,
  `gemeente` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `civicrm_autorelationship_local_member`
--

CREATE TABLE IF NOT EXISTS `civicrm_autorelationship_local_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;