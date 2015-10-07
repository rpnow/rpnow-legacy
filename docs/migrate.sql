CREATE TABLE `Room_Migration` (
  `Old_Id` CHAR(4) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
  `New_Id` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
  PRIMARY KEY (`Old_Id`)
) ENGINE = InnoDB;
ALTER TABLE `Room_Migration`
  ADD UNIQUE KEY `New_Id` (`New_Id`);

/* RUN PHP MIGRATION FUNCTION */

UPDATE `Room` RIGHT JOIN `Room_Migration` ON (`Room`.`ID` = `Room_Migration`.`Old_Id`) SET `Room`.`ID` = `Room_Migration`.`New_Id`;