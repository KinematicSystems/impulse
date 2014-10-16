/*
	This is test data for the DAO unit tests.  The properties and system_setting tables
	contain data that is application specific and will never be populated programatially.
*/
-- Test data for properties table
INSERT INTO `impulse`.`properties`(`id`,`section`,`description`)
VALUES("TestProp.1","TestProperties","TestProp.1 Description");
INSERT INTO `impulse`.`properties`(`id`,`section`,`description`)
VALUES("TestProp.2","TestProperties","TestProp.2 Description");
INSERT INTO `impulse`.`properties`(`id`,`section`,`description`)
VALUES("TestProp.3","TestProperties","TestProp.3 Description");
INSERT INTO `impulse`.`properties`(`id`,`section`,`description`)
VALUES("TestProp.4","TestProperties","TestProp.4 Description");
INSERT INTO `impulse`.`properties`(`id`,`section`,`description`)
VALUES("TestProp.5","TestProperties","TestProp.5 Description");
INSERT INTO `impulse`.`properties`(`id`,`section`,`description`)
VALUES("TestProp.6","TestProperties","TestProp.6 Description");

INSERT INTO `impulse`.`properties`(`id`,`section`,`description`)
VALUES("OtherProp.1","OtherProperties","OtherProp.1 Description");
INSERT INTO `impulse`.`properties`(`id`,`section`,`description`)
VALUES("OtherProp.2","OtherProperties","OtherProp.2 Description");
INSERT INTO `impulse`.`properties`(`id`,`section`,`description`)
VALUES("OtherProp.3","OtherProperties","OtherProp.3 Description");

INSERT INTO `impulse`.`properties`(`id`,`section`,`description`)
VALUES("MoreProp.1","MoreProperties","MoreProp.1 Description");
INSERT INTO `impulse`.`properties`(`id`,`section`,`description`)
VALUES("MoreProp.2","MoreProperties","MoreProp.2 Description");
INSERT INTO `impulse`.`properties`(`id`,`section`,`description`)
VALUES("MoreProp.3","MoreProperties","MoreProp.3 Description");
INSERT INTO `impulse`.`properties`(`id`,`section`,`description`)
VALUES("MoreProp.4","MoreProperties","MoreProp.4 Description");

-- Test data for system_setting table
INSERT INTO `impulse`.`system_setting`(`domain`,`settingKey`,`value`,`type`,`parent`,`description`)
VALUES ("TestDomain.1","D1.TestKey.1","D1.TestVal.1","STRING",NULL,"D1.TestKey.1 Description");
INSERT INTO `impulse`.`system_setting`(`domain`,`settingKey`,`value`,`type`,`parent`,`description`)
VALUES ("TestDomain.1","D1.TestKey.2","D1.TestVal.2","STRING",NULL,"D1.TestKey.2 Description");
INSERT INTO `impulse`.`system_setting`(`domain`,`settingKey`,`value`,`type`,`parent`,`description`)
VALUES ("TestDomain.1","D1.TestKey.3","D1.TestVal.3","STRING",NULL,"D1.TestKey.3 Description");

-- A parent child relationship is set by setting 
-- child.parent = parent.domain and child.domain = parent.value 
INSERT INTO `impulse`.`system_setting`(`domain`,`settingKey`,`value`,`type`,`parent`,`description`)
VALUES ("ParentDomain","ParentKey.1","ChildDomain","STRING",NULL,"ParentKey.1 Description");
INSERT INTO `impulse`.`system_setting`(`domain`,`settingKey`,`value`,`type`,`parent`,`description`)
VALUES ("ChildDomain","ChildKey.1","1111","NUMBER","ParentDomain","ChildKey.1 Description");
INSERT INTO `impulse`.`system_setting`(`domain`,`settingKey`,`value`,`type`,`parent`,`description`)
VALUES ("ChildDomain","ChildKey.2","2222","NUMBER","ParentDomain","ChildKey.2 Description");
INSERT INTO `impulse`.`system_setting`(`domain`,`settingKey`,`value`,`type`,`parent`,`description`)
VALUES ("ChildDomain","ChildKey.3","3333","NUMBER","ParentDomain","ChildKey.3 Description");



