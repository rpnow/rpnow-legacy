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

ALTER TABLE `Room` CHANGE `ID` `ID` CHAR(4) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;
ALTER TABLE `Message` CHANGE `Room` `Room` CHAR(4) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;
ALTER TABLE `Character` CHANGE `Room` `Room` CHAR(4) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;