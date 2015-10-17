<?php

if(!isset($rpVersion)) die();

require_once 'config.php';
  
class Admin {
  public static function RecentActivity($maxRows) {
    $conn = RPDatabase::createConnection();
    return $conn->query("SELECT
      `Title`,
      `ID`,
      `Time_Created`,
      `IP`,
      (SELECT COALESCE(MAX(`Time_Created`), `Room`.`Time_Created`) FROM `Message` WHERE `Message`.`Room_Number` = `Room`.`Number`) AS `Time_Updated`,
      (SELECT COUNT(*) FROM `Message` WHERE `Message`.`Room_Number` = `Room`.`Number`) AS `Num_Msgs`
      FROM `Room`
      ORDER BY `Time_Updated` DESC LIMIT $maxRows"
    )->fetchAll();
  }
  
  public static function NewestRooms($maxRows) {
    $conn = RPDatabase::createConnection();
    return $conn->query("SELECT
      `Title`,
      `ID`,
      `Time_Created`,
      `IP`,
      (SELECT COALESCE(MAX(`Time_Created`), `Room`.`Time_Created`) FROM `Message` WHERE `Message`.`Room_Number` = `Room`.`Number`) AS `Time_Updated`,
      (SELECT COUNT(*) FROM `Message` WHERE `Message`.`Room_Number` = `Room`.`Number`) AS `Num_Msgs`
      FROM `Room`
      ORDER BY `Time_Created` DESC LIMIT $maxRows"
    )->fetchAll();
  }
  
  public static function TopRPs($secs, $maxRows) {
    $conn = RPDatabase::createConnection();
    return $conn->query("SELECT * FROM
      (SELECT
        `Title`,
        `ID`,
        `Time_Created`,
        `IP`,
        (SELECT COALESCE(MAX(`Time_Created`), `Room`.`Time_Created`) FROM `Message` WHERE `Message`.`Room_Number` = `Room`.`Number`) AS `Time_Updated`,
        (SELECT COUNT(*) FROM `Message` WHERE `Message`.`Room_Number` = `Room`.`Number`) AS `Num_Msgs`,
        (SELECT COUNT(*) FROM `Message` WHERE `Message`.`Room_Number` = `Room`.`Number` AND `Message`.`Time_Created` > DATE_SUB(NOW(), INTERVAL $secs SECOND)) AS `Num_Recent_Msgs`
        FROM `Room`
      ) AS `Dataset`
      WHERE `Num_Recent_Msgs` > 0
      ORDER BY `Num_Recent_Msgs` DESC LIMIT $maxRows"
    )->fetchAll();
  }
  
  public static function AllTimeTopRPs($maxRows) {
    $conn = RPDatabase::createConnection();
    return $conn->query("SELECT * FROM
      (SELECT
        `Title`,
        `ID`,
        `Time_Created`,
        `IP`,
        (SELECT COALESCE(MAX(`Time_Created`), `Room`.`Time_Created`) FROM `Message` WHERE `Message`.`Room_Number` = `Room`.`Number`) AS `Time_Updated`,
        (SELECT COUNT(*) FROM `Message` WHERE `Message`.`Room_Number` = `Room`.`Number`) AS `Num_Msgs`
        FROM `Room`
      ) AS `Dataset`
      WHERE `Num_Msgs` > 0
      ORDER BY `Num_Msgs` DESC LIMIT $maxRows"
    )->fetchAll();
  }
  
  public static function LongestDuration($maxRows) {
    $conn = RPDatabase::createConnection();
    return $conn->query("SELECT
      `Title`,
      `ID`,
      `Time_Created`,
      `IP`,
      (SELECT COALESCE(MAX(`Time_Created`), `Room`.`Time_Created`) FROM `Message` WHERE `Message`.`Room_Number` = `Room`.`Number`) AS `Time_Updated`,
      (SELECT COUNT(*) FROM `Message` WHERE `Message`.`Room_Number` = `Room`.`Number`) AS `Num_Msgs`,
      (SELECT TIMESTAMPDIFF(SECOND, `Time_Created`,`Time_Updated`)) AS `Timespan`
      FROM `Room`
      ORDER BY `Timespan` DESC LIMIT $maxRows"
    )->fetchAll();
  }
  
  public static function SearchTitles($query, $maxRows) {
    $conn = RPDatabase::createConnection();
    $query = "%$query%";
    $statement = $conn->prepare("SELECT
      `Title`,
      `ID`,
      `Time_Created`,
      `IP`,
      (SELECT COALESCE(MAX(`Time_Created`), `Room`.`Time_Created`) FROM `Message` WHERE `Message`.`Room_Number` = `Room`.`Number`) AS `Time_Updated`,
      (SELECT COUNT(*) FROM `Message` WHERE `Message`.`Room_Number` = `Room`.`Number`) AS `Num_Msgs`
      FROM `Room`
      WHERE `Title` LIKE ?
      ORDER BY `Time_Created` DESC LIMIT $maxRows"
    );
    $statement->execute(array($query));
    return $statement->fetchAll();
  }
  
  public static function SearchFull($query, $maxRows) {
    $conn = RPDatabase::createConnection();
    $query = "%$query%";
    $statement = $conn->prepare("SELECT
      `Room`.`Title`,
      `Room`.`ID`,
      `Room`.`Time_Created`,
      `Room`.`IP`,
      `Room`.`ID`,
      `Room`.`Number`,
      COUNT(*) AS `Found_Count`
      FROM `Message` LEFT JOIN `Room` ON (
        `Room`.`Number` = `Message`.`Room_Number`
      ) WHERE `Message`.`Content` LIKE ?
      GROUP BY `Room`.`ID`
      ORDER BY `Found_Count` DESC LIMIT $maxRows"
    );
    $statement->execute(array($query));
    return $statement->fetchAll();
  }
}

?>