DROP TABLE IF EXISTS `group_package`;
CREATE TABLE `group_package` (
  `id` int(11) NOT NULL,
  `packageid` int(50) NOT NULL,
  `groupid` int(11) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
	`efffrom` int(11) DEFAULT NULL,
  `effto` int(11) DEFAULT NULL,
  `createdby` int(11) DEFAULT NULL,
  `updatedby` int(11) DEFAULT NULL,
  `createdtime` int(11) DEFAULT NULL,
  `updatedtime` int(11) DEFAULT NULL,
  `sysdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `maxhours` int(11) DEFAULT NULL,
  `maxvisitors` int(11) DEFAULT NULL,
  `maxdays` int(11) DEFAULT NULL,
  `maxprintpapers` int(11) DEFAULT NULL,
  `maxmeetinghours` int(11) DEFAULT NULL,
  `allowcredit` tinyint(4) NOT NULL DEFAULT '1',
	`discountbar` tinyint(3) UNSIGNED DEFAULT NULL,
  `price` int(10) UNSIGNED NOT NULL,
  `locker` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `group_package`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `group_package`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `group_package`  ADD `visitorprice` INT NULL DEFAULT NULL ;
ALTER TABLE `group_package`  ADD `efftoextend` INT NULL DEFAULT NULL ;
ALTER TABLE `group_package`  ADD `printedpapers` INT NULL DEFAULT NULL ;
ALTER TABLE `group_package`  ADD `regionid` text NULL DEFAULT NULL ;
