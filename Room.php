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
    self::conn()->query("INSERT INTO `Character` (`Name`, `Room`, `Color`) VALUES ('Narrator', '$id', '#ddd')");
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
  
  public function send($name, $content, $isAction = false) {
    $name = self::conn()->real_escape_string($name);
    $content = self::conn()->real_escape_string($content);
    $isAction = boolval($isAction)? '1': '0';
    $room = $this->getID();
    $result = self::conn()->query("INSERT INTO `Message` (`Character_Name`, `Character_Room`, `Content`, `Is_Action`) VALUES ('$name', '$room', '$content', '$isAction')");
  }
  
  public function getMessages($after = 0) {
    if(!is_int($after) || $after < 0) {
      throw new Exception("Value for 'after' must be a positive integer.");
    }
    $room = $this->getID();
    $result = self::conn()->query("SELECT `Content`, `Is_Action`, `Timestamp`, `Name`, `Color` FROM `Message` LEFT JOIN `Character` ON (`Character_Name` = `Name` AND `Character_Room` = `Room`)  WHERE `Number` > '$after' AND `Character_Room` = '$room' ORDER BY `Number` ASC");
    return $result->fetch_all(MYSQLI_ASSOC); 
  }
  
  public function getCharacters() {
    // get the characters
    $room = $this->getID();
    $result = self::conn()->query("SELECT `Name`, `Color` FROM `Character` WHERE `Room` = '$room'");
    // calculate the secondary color for each and return in modified array
    return array_map(
      function($x) {
        //YIQ algorithm retrieved from:
        // http://24ways.org/2010/calculating-color-contrast/
        $r = hexdec(substr($x['Color'],1,2));
        $g = hexdec(substr($x['Color'],3,2));
        $b = hexdec(substr($x['Color'],5,2));
        $yiq = (($r*299)+($g*587)+($b*114))/1000;
        return array(
          'Name' => $x['Name'],
          'Color' => $x['Color'],
          'Contrast' => ($yiq >= 128) ? 'black' : 'white'
        );
      },
      $result->fetch_all(MYSQLI_ASSOC)
    );
  }
  
  public function addCharacter($name, $color) {
    $name = self::conn()->real_escape_string($name);
    if(!preg_match_all('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
      throw new Exception("$color is not a valid hex color.");
    }
    $room = $this->getID();
    $result = self::conn()->query("INSERT INTO `Character` (`Name`, `Room`, `Color`) VALUES ('$name', '$room', '$color')");
  }
}

?>