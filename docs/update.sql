/* rp.js update */

ALTER TABLE `Message` CHANGE `Timestamp` `Time_Created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `Message` ADD `Deleted` BOOLEAN NOT NULL DEFAULT FALSE ;
ALTER TABLE `Message` ADD `Time_Updated` TIMESTAMP NULL AFTER `Time_Created`;
ALTER TABLE `Message` ADD `Chara_Number` INT NULL AFTER `Time_Updated`; 
UPDATE `Message` SET `Chara_Number` = (SELECT `Number` FROM `Character` WHERE `Message`.`Room` = `Character`.`Room` AND `Character`.`Name` = `Character_Name`);
ALTER TABLE `Message` DROP `Character_Name`;

ALTER TABLE `Character` CHANGE `Timestamp` `Time_Created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `Character` ADD `Deleted` BOOLEAN NOT NULL DEFAULT FALSE ;
ALTER TABLE `Character` ADD `Time_Updated` TIMESTAMP NULL AFTER `Time_Created`;

ALTER TABLE `Room` CHANGE `Timestamp` `Time_Created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

/* update update */

CREATE TABLE `Message_Update` (
  `Number` int(11) NOT NULL,
  `Message_Number` int(11) NOT NULL,
  `Action` enum('delete','undelete') NOT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IP` varchar(45) NOT NULL
);

ALTER TABLE `Message_Update` ADD PRIMARY KEY (`Number`);
ALTER TABLE `Message_Update` MODIFY `Number` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `Chara_Update` (
  `Number` int(11) NOT NULL,
  `Chara_Number` int(11) NOT NULL,
  `Action` enum('delete','undelete') NOT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IP` varchar(45) NOT NULL
);

ALTER TABLE `Chara_Update` ADD PRIMARY KEY (`Number`);
ALTER TABLE `Chara_Update` MODIFY `Number` int(11) NOT NULL AUTO_INCREMENT;

/* binary collation room id update */

ALTER TABLE `Room` CHANGE `ID` `ID` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;
ALTER TABLE `Message` CHANGE `Room` `Room` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;
ALTER TABLE `Character` CHANGE `Room` `Room` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;

/* room autonumber ID update */

ALTER TABLE `Room` DROP PRIMARY KEY, ADD UNIQUE `ID` (`ID`);
ALTER TABLE `Room` ADD `Number` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`Number`) ;

ALTER TABLE `Message` ADD `Room_Number` INT NOT NULL AFTER `Room`;
UPDATE `Message` SET `Room_Number` = (SELECT `Number` FROM `Room` WHERE `Message`.`Room` = `Room`.`ID`);
ALTER TABLE `Message` DROP INDEX `Character_Room`;
ALTER TABLE `Message` DROP INDEX `Character`;
ALTER TABLE `Message` DROP `Room`;
ALTER TABLE `Message` ADD INDEX(`Room_Number`);

ALTER TABLE `Character` ADD `Room_Number` INT NOT NULL AFTER `Room`;
UPDATE `Character` SET `Room_Number` = (SELECT `Number` FROM `Room` WHERE `Character`.`Room` = `Room`.`ID`);
ALTER TABLE `Character` DROP INDEX `NameRoom`;
ALTER TABLE `Character` DROP INDEX `Room`;
ALTER TABLE `Character` DROP `Room`;
ALTER TABLE `Character` ADD INDEX(`Room_Number`);
