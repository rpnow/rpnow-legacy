<?php
require_once 'config.php';

class Room {
  private static $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  
  private static $_conn = NULL;
  private static function conn() {
    if(static::$_conn === NULL) {
      global $DBServer, $DBUser, $DBPass, $DBName;
      static::$_conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);
      if(static::$_conn->connect_error) {
        trigger_error('Database connection failed: '  . $_conn->connect_error, E_USER_ERROR);
      }
    }
    return static::$_conn;
  }
  
  private $id;
  private $title;
  private $desc;
  
  private function __construct($id, $title, $desc) {
    $this->id = $id;
    $this->title = $title;
    $this->desc = $desc;
  }
  
  public static function CreateRoom($title, $desc) {
    global $RoomIDLen;
    self::conn()->autocommit(false);
    do {
      $id = '';
      for ($i = 0; $i < $RoomIDLen; $i++) {
        $id .= self::$characters[rand(0, strlen(self::$characters) - 1)];
      }
    } while(Room::IDExists($id));
    $title = self::conn()->real_escape_string($title);
    $desc = self::conn()->real_escape_string($desc);
    self::conn()->query("INSERT INTO `Room` (`ID`, `Title`, `Description`) VALUES ('$id', '$title', '$desc')");
    self::conn()->commit();
    return new Room($id, $title, $desc);
  }
  
  public static function GetRoom($id) {
    if(!Room::IsValidID($id)) {
      throw new Exception('Malformed Room ID.');
    }
    $result = self::conn()->query("SELECT `Title`, `Description` FROM `Room` WHERE `ID` = '$id'");
    if($result->num_rows == 0) {
      throw new Exception("Room '$id' does not exist.");
    }
    $row = $result->fetch_assoc();
    return new Room($id, $row['Title'], $row['Description']);
  }
  
  public function getID() { return $this->id; }
  public function getTitle() { return $this->title; }
  public function getDesc() { return $this->desc; }
  
  private static function IsValidID($id) {
    global $RoomIDLen;
    return ctype_alnum($id) && strlen($id) == $RoomIDLen;
  }
  
  // CAUTION: only run if you're SURE it's not a malformed ID! could be catastrophic otherwise
  private static function IDExists($id) {
    $result = self::conn()->query("SELECT COUNT(*) FROM `Room` WHERE `ID` = '$id' LIMIT 1");
    var_dump($result);
    $row = $result->fetch_array();
    return $row[0] == '1';
  }
}

?>