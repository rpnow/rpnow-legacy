<?php
require_once 'config.php';

class Room {
  private static $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  
  private static function createConnection() {
    global $rpDBServer, $rpDBUser, $rpDBPass, $rpDBName;
    $myConn = new mysqli($rpDBServer, $rpDBUser, $rpDBPass, $rpDBName);
    if($myConn->connect_error) {
      trigger_error('Database connection failed: '  . $myConn->connect_error, E_USER_ERROR);
    }
    return $myConn;
  }
  
  private $db;
  private $id;
  private $title;
  private $desc;
  private $numMsgs;
  private $numChars;
  
  private function __construct($db, $id, $title, $desc, $numChars, $numMsgs) {
    $this->db = $db;
    $this->id = $id;
    $this->title = $title;
    $this->desc = $desc;
    $this->numMsgs = $numMsgs;
    $this->numChars = $numChars;
  }
  
  public static function CreateRoom($title, $desc) {
    global $rpIDLength;
    $conn = self::createConnection();
    $conn->autocommit(false);
    do {
      $id = '';
      for ($i = 0; $i < $rpIDLength; $i++) {
        $id .= self::$characters[rand(0, strlen(self::$characters) - 1)];
      }
    } while(Room::IDExists($id, $conn));
    $title = $conn->real_escape_string($title);
    $desc = $conn->real_escape_string($desc);
    $conn->query("INSERT INTO `Room` (`ID`, `Title`, `Description`) VALUES ('$id', '$title', '$desc')");
    $conn->query("INSERT INTO `Character` (`Name`, `Room`, `Color`) VALUES ('Narrator', '$id', '#ddd')");
    return new Room($conn, $id, $title, $desc, 1, 0);
  }
  
  public static function GetRoom($id) {
    if(!Room::IsValidID($id)) {
      throw new Exception('Malformed Room ID.');
    }
    $conn = self::createConnection();
    $conn->autocommit(false);
    $result = $conn->query("SELECT
    (SELECT `Title` FROM `Room` WHERE `ID` = '$id') AS `Title`,
    (SELECT `Description` FROM `Room` WHERE `ID` = '$id') AS `Description`,
    (SELECT COUNT(*) FROM `Character` WHERE `Room` = '$id') AS `CharacterCount`,
    (SELECT COUNT(*) FROM `Message` WHERE `Character_Room` = '$id') AS `MessageCount`");
    if($result->num_rows == 0) {
      throw new Exception("Room '$id' does not exist.");
    }
    $row = $result->fetch_assoc();
    return new Room($conn, $id, $row['Title'], $row['Description'], $row['CharacterCount'], $row['MessageCount']);
  }
  
  public function close() {
    $this->db->commit();
    $this->db->close();
  }
  
  public function getID() { return $this->id; }
  public function getTitle() { return $this->title; }
  public function getDesc() { return $this->desc; }
  public function getMessageCount() { return $this->numMsgs; }
  public function getCharacterCount() { return $this->numChars; }
  
  private static function IsValidID($id) {
    global $rpIDLength;
    return ctype_alnum($id) && strlen($id) == $rpIDLength;
  }
  
  // CAUTION: only run if you're SURE it's not a malformed ID! could be catastrophic otherwise
  private static function IDExists($id, $conn) {
    $result = $conn->query("SELECT COUNT(*) FROM `Room` WHERE `ID` = '$id' LIMIT 1");
    if(!$result) {
      throw new Exception($conn->error);
    }
    $row = $result->fetch_array();
    return $row[0] == '1';
  }
  
  public function getMessages($page) {
    $room = $this->getID();
    global $rpPostsPerPage;
    $result = NULL;
    if($page == 'latest') {
      $result = $this->db->query("(SELECT `Content`, `Is_Action`, `Timestamp`, `Character_Name` AS `Name`, `Number` FROM `Message` WHERE `Character_Room` = '$room' ORDER BY `Number` DESC LIMIT $rpPostsPerPage) ORDER BY `Number` ASC;");
    }
    else if($page == 'all') {
      $result = $this->db->query("SELECT `Content`, `Is_Action`, `Timestamp`, `Character_Name` AS `Name`, `Number` FROM `Message` WHERE `Character_Room` = '$room' ORDER BY `Number` ASC;");
    }
    else {
      if(intval($page) == false || intval($page) != floatval($page) || intval($page) < 1) {
        throw new Exception('invalid page number.');
      }
      $page = intval($page);
      if($page > 1 && $page > $this->getNumPages()) {
        throw new Exception('page does not yet exist.');
      }
      $start = ($page - 1) * $rpPostsPerPage;
      $result = $this->db->query("SELECT `Content`, `Is_Action`, `Timestamp`, `Character_Name` AS `Name` FROM `Message` WHERE `Character_Room` = '$room' ORDER BY `Number` ASC LIMIT $start, $rpPostsPerPage;");
    }
    if(!$result) {
      throw new Exception($conn->error);
    }
    $arr = [];
    while ($row = $result->fetch_assoc()) {
      $arr[] = $row;
    }
    return $arr;
  }
  
  public function getCharacters() {
    // get the characters
    $room = $this->getID();
    $result = $this->db->query("SELECT `Name`, `Color` FROM `Character` WHERE `Room` = '$room'");
    $arr = [];
    while ($row = $result->fetch_assoc()) {
      $arr[] = $row;
    }
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
      $arr
    );
  }
  
  public function getUpdates($numMessages, $numCharacters) {
    $room = $this->getID();
    if(intval($numMessages) === false || intval($numMessages) != floatval($numMessages) || intval($numMessages) < 0) {
      throw new Exception('invalid numMessages.');
    }
    if(intval($numCharacters) === false || intval($numCharacters) != floatval($numCharacters) || intval($numCharacters) < 0) {
      throw new Exception('invalid numCharacters.');
    }
    $newMessages = $this->db->query("SELECT `Content`, `Is_Action`, `Timestamp`, `Character_Name` AS `Name` FROM `Message` WHERE `Character_Room` = '$room' ORDER BY `Number` ASC LIMIT 999 OFFSET $numMessages");
    $messageArray = [];
    while ($row = $newMessages->fetch_assoc()) {
      $messageArray[] = $row;
    }
    $newChars = $this->db->query("SELECT `Name`, `Color` FROM `Character` WHERE `Room` = '$room' ORDER BY `Number` ASC LIMIT 999 OFFSET $numCharacters");
    $charaArray = [];
    while ($row = $newChars->fetch_assoc()) {
      $charaArray[] = $row;
    }
    return array('messages' => $messageArray, 'characters' => $charaArray);
  }
  
  public function getNumPages() {
    global $rpPostsPerPage;
    return ceil($this->getMessageCount() / $rpPostsPerPage);
  }
  
  public function getStatsArray() {
    $room = $this->getID();
    return array_merge(
      $this->db->query("SELECT
        (SELECT MAX(`Timestamp`) FROM `Message` WHERE `Character_Room`='$room') AS `LatestMessageDate`,
        (SELECT MIN(`Timestamp`) FROM `Message` WHERE `Character_Room`='$room') AS `FirstMessageDate`"
      )->fetch_assoc(),
      array('MessageCount' => $this->getMessageCount(), 'CharacterCount' => $this->getCharacterCount())
    );
  }
  
  public function send($name, $content, $isAction = false) {
    $name = $this->db->real_escape_string(trim($name));
    $content = $this->db->real_escape_string(trim($content));
    if(!$content) {
      throw new Exception('Message is empty.');
    }
    $isAction = $isAction? '1': '0';
    $room = $this->getID();
    $result = $this->db->query("INSERT INTO `Message` (`Character_Name`, `Character_Room`, `Content`, `Is_Action`) VALUES ('$name', '$room', '$content', '$isAction')");
  }
  
  public function addCharacter($name, $color) {
    $name = $this->db->real_escape_string(trim($name));
    if(!$name) {
      throw new Exception('Name is empty.');
    }
    if(!preg_match_all('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
      throw new Exception("$color is not a valid hex color.");
    }
    $room = $this->getID();
    $result = $this->db->query("INSERT INTO `Character` (`Name`, `Room`, `Color`) VALUES ('$name', '$room', '$color')");
  }
}

?>