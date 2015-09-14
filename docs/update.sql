ALTER TABLE `message` CHANGE `Timestamp` `Time_Created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `message` ADD `Deleted` BOOLEAN NOT NULL DEFAULT FALSE ;
ALTER TABLE `message` ADD `Time_Updated` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `Time_Created`;
UPDATE `message` SET `Time_Updated` = `Time_Created`;
ALTER TABLE `message` ADD `Chara_Number` INT NULL AFTER `Time_Updated`; 
UPDATE `message` SET `Chara_Number` = (SELECT `Number` FROM `character` WHERE `message`.`Room` = `character`.`Room` AND `character`.`Name` = `Character_Name`);
ALTER TABLE `message` DROP `Character_Name`;

ALTER TABLE `character` CHANGE `Timestamp` `Time_Created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `character` ADD `Deleted` BOOLEAN NOT NULL DEFAULT FALSE ;
ALTER TABLE `character` ADD `Time_Updated` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `Time_Created`;
UPDATE `character` SET `Time_Updated` = `Time_Created`;

ALTER TABLE `room` CHANGE `Timestamp` `Time_Created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

CREATE TABLE `message_update` (
  `Number` int(11) NOT NULL,
  `Message_Number` int(11) NOT NULL,
  `Action` enum('delete','undelete') NOT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IP` varchar(45) NOT NULL
);

ALTER TABLE `message_update` ADD PRIMARY KEY (`Number`);
ALTER TABLE `message_update` MODIFY `Number` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `chara_update` (
  `Number` int(11) NOT NULL,
  `Chara_Number` int(11) NOT NULL,
  `Action` enum('delete','undelete') NOT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IP` varchar(45) NOT NULL
);

ALTER TABLE `chara_update` ADD PRIMARY KEY (`Number`);
ALTER TABLE `chara_update` MODIFY `Number` int(11) NOT NULL AUTO_INCREMENT;