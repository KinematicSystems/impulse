/*
	This is test data for the DAO unit tests.  The properties and system_setting tables
	contain data that is application specific and will never be created via a UI.
*/
-- Test data for properties table
INSERT INTO `properties`(`id`,`section`,`description`)
VALUES("TestProp.1","TestProperties","TestProp.1 Description");
INSERT INTO `properties`(`id`,`section`,`description`)
VALUES("TestProp.2","TestProperties","TestProp.2 Description");
INSERT INTO `properties`(`id`,`section`,`description`)
VALUES("TestProp.3","TestProperties","TestProp.3 Description");
INSERT INTO `properties`(`id`,`section`,`description`)
VALUES("TestProp.4","TestProperties","TestProp.4 Description");
INSERT INTO `properties`(`id`,`section`,`description`)
VALUES("TestProp.5","TestProperties","TestProp.5 Description");
INSERT INTO `properties`(`id`,`section`,`description`)
VALUES("TestProp.6","TestProperties","TestProp.6 Description");

INSERT INTO `properties`(`id`,`section`,`description`)
VALUES("OtherProp.1","OtherProperties","OtherProp.1 Description");
INSERT INTO `properties`(`id`,`section`,`description`)
VALUES("OtherProp.2","OtherProperties","OtherProp.2 Description");
INSERT INTO `properties`(`id`,`section`,`description`)
VALUES("OtherProp.3","OtherProperties","OtherProp.3 Description");

INSERT INTO `properties`(`id`,`section`,`description`)
VALUES("MoreProp.1","MoreProperties","MoreProp.1 Description");
INSERT INTO `properties`(`id`,`section`,`description`)
VALUES("MoreProp.2","MoreProperties","MoreProp.2 Description");
INSERT INTO `properties`(`id`,`section`,`description`)
VALUES("MoreProp.3","MoreProperties","MoreProp.3 Description");
INSERT INTO `properties`(`id`,`section`,`description`)
VALUES("MoreProp.4","MoreProperties","MoreProp.4 Description");

INSERT INTO `system_setting` (`domain`,`settingKey`,`value`,`type`,`parent`,`description`) VALUES ('Domain1','Setting1','Value1','text',NULL,NULL);
INSERT INTO `system_setting` (`domain`,`settingKey`,`value`,`type`,`parent`,`description`) VALUES ('Domain1','Setting2','Value2','text',NULL,NULL);
INSERT INTO `system_setting` (`domain`,`settingKey`,`value`,`type`,`parent`,`description`) VALUES ('Domain2','Setting3','Value3','text',NULL,NULL);
INSERT INTO `system_setting` (`domain`,`settingKey`,`value`,`type`,`parent`,`description`) VALUES ('Domain2','Setting4','123','number',NULL,NULL);
INSERT INTO `system_setting` (`domain`,`settingKey`,`value`,`type`,`parent`,`description`) VALUES ('Domain2','Setting5','http://www.kinematicsystems.com','url',NULL,NULL);
INSERT INTO `system_setting` (`domain`,`settingKey`,`value`,`type`,`parent`,`description`) VALUES ('Domain2','Setting6','222','number',NULL,NULL);
INSERT INTO `system_setting` (`domain`,`settingKey`,`value`,`type`,`parent`,`description`) VALUES ('Domain3','Setting7','Value7','number',NULL,NULL);
INSERT INTO `system_setting` (`domain`,`settingKey`,`value`,`type`,`parent`,`description`) VALUES ('GoogleMap','Attribution','1234','number','Map',NULL);
INSERT INTO `system_setting` (`domain`,`settingKey`,`value`,`type`,`parent`,`description`) VALUES ('GoogleMap','CRS','5679','number','Map',NULL);
INSERT INTO `system_setting` (`domain`,`settingKey`,`value`,`type`,`parent`,`description`) VALUES ('GoogleMap','URL','http://www.google.com','url','Map',NULL);
INSERT INTO `system_setting` (`domain`,`settingKey`,`value`,`type`,`parent`,`description`) VALUES ('Map','ActiveMap','GoogleMap','text',NULL,NULL);



