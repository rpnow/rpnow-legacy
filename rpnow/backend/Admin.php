<?php

if(!isset($rpVersion)) die();

require_once 'config.php';
  
class Admin {
  public static function AuditRooms() {
    $conn = RPDatabase::createConnection();
    return $conn->query("SELECT
    `Title`,
    `ID`,
    `Time_Created`,
    `IP`,
    (SELECT COALESCE(MAX(`Time_Created`), `Room`.`Time_Created`) FROM `Message` WHERE `Message`.`Room_Number` = `Room`.`Number`) AS `Time_Updated`,
    (SELECT COUNT(*) FROM `Message` WHERE `Message`.`Room_Number` = `Room`.`Number`) AS `Num_Msgs`
    FROM `Room`
    ORDER BY `Time_Updated` DESC");
  }
}

?>