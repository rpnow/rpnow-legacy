<?php
require_once 'config.php';

class Room {
  private static $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  
  private static function createConnection() {
    global $rpDBServer, $rpDBUser, $rpDBPass, $rpDBName;
    $dsn = 'mysql:host=' . $rpDBServer . ';dbname=' . $rpDBName;
    $myConn = new PDO($dsn, $rpDBUser, $rpDBPass);
    $myConn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    $myConn->beginTransaction();
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
    do {
      $id = '';
      for ($i = 0; $i < $rpIDLength; $i++) {
        $id .= self::$characters[rand(0, strlen(self::$characters) - 1)];
      }
    } while(Room::IDExists($id, $conn));
    $conn
      ->prepare("INSERT INTO `Room` (`ID`, `Title`, `Description`, `IP`) VALUES (?, ?, ?, ?)")
      ->execute(array($id, $title, $desc, $_SERVER['REMOTE_ADDR']));
    return new Room($conn, $id, $title, $desc, 1, 0);
  }
  
  public static function GetRoom($id) {
    if(!Room::IsValidID($id)) {
      throw new Exception('Malformed Room ID.');
    }
    $conn = self::createConnection();
    if(!Room::IDExists($id, $conn)) {
      throw new Exception('Room does not exist!');
    }
    $statement = $conn->prepare("SELECT
      (SELECT `Title` FROM `Room` WHERE `ID` = :id) AS `Title`,
      (SELECT `Description` FROM `Room` WHERE `ID` = :id) AS `Description`,
      (SELECT COUNT(*) FROM `Character` WHERE `Room` = :id) AS `CharacterCount`,
      (SELECT COUNT(*) FROM `Message` WHERE `Room` = :id) AS `MessageCount`");
    $statement->execute(array('id'=>$id));
    if($statement->rowCount() == 0) {
      throw new Exception("Room '$id' does not exist.");
    }
    $row = $statement->fetch();
    return new Room($conn, $id, $row['Title'], $row['Description'], +$row['CharacterCount'], +$row['MessageCount']);
  }
  
  public static function AuditRooms() {
    $conn = self::createConnection();
    return $conn->query("SELECT
    `Title`,
    `ID`,
    `Timestamp` AS `Created`,
    `IP`,
    (SELECT COALESCE(MAX(`Timestamp`), `Room`.`Timestamp`) FROM `Message` WHERE `Room` = `ID`) AS `Updated`,
    (SELECT COUNT(*) FROM `Message` WHERE `Room` = `ID`) AS `Num_Msgs`
    FROM `Room`
    ORDER BY `Updated` DESC");
  }
  
  public function close() {
    $this->db->commit();
    $this->db = null;
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
  
  private static function IDExists($id, $conn) {
    $statement = $conn->prepare("SELECT COUNT(*) FROM `Room` WHERE `ID` = ? LIMIT 1");
    $statement->execute(array($id));
    $row = $statement->fetch();
    return $row[0] == '1';
  }
  
  public function getMessages($which, $n = NULL) {
    $room = $this->getID();
    global $rpPostsPerPage;
    $statement = NULL;
    if($which == 'latest') {
      $statement = $this->db->prepare("(SELECT
      `Type`, `Content`, UNIX_TIMESTAMP(`Timestamp`) AS `Timestamp`, `Character_Name` AS `Name`,
      `Number`
      FROM `Message` WHERE `Room` = '$room'
      ORDER BY `Number` DESC LIMIT $rpPostsPerPage)
      ORDER BY `Number` ASC;");
    }
    else if($which == 'all') {
      $statement = $this->db->prepare("SELECT
      `Type`, `Content`, UNIX_TIMESTAMP(`Timestamp`) AS `Timestamp`, `Character_Name` AS `Name`
      FROM `Message` WHERE `Room` = '$room'
      ORDER BY `Number` ASC;");
    }
    else if($which == 'page' && !is_null($n)) {
      if(intval($n) == false || intval($n) != floatval($n) || intval($n) < 1) {
        throw new Exception('invalid page number.');
      }
      $n = intval($n);
      if($n > 1 && $n > $this->getNumPages()) {
        throw new Exception('page does not yet exist.');
      }
      $start = ($n - 1) * $rpPostsPerPage;
      $statement = $this->db->prepare("SELECT
      `Type`, `Content`, UNIX_TIMESTAMP(`Timestamp`) AS `Timestamp`, `Character_Name` AS `Name`
      FROM `Message` WHERE `Room` = '$room'
      ORDER BY `Number` ASC LIMIT $start, $rpPostsPerPage;");
    }
    else if($which == 'after' && !is_null($n)) {
      if(intval($n) === false || intval($n) != floatval($n) || intval($n) < 0) {
        throw new Exception("invalid message request: $n is a bad number.");
      }
      $statement = $this->db->prepare("SELECT
      `Type`, `Content`, UNIX_TIMESTAMP(`Timestamp`) AS `Timestamp`, `Character_Name` AS `Name`
      FROM `Message` WHERE `Room` = '$room'
      ORDER BY `Number` ASC LIMIT 9999 OFFSET $n");
    }
    else {
      throw new Exception('unknown message request!');
    }
    $statement->execute();
    return $statement->fetchAll();
  }
  
  public function getCharacters($after = 0) {
    if(intval($after) === false || intval($after) != floatval($after) || intval($after) < 0) {
      throw new Exception("invalid character request: $after is a bad number.");
    }
    // get the characters
    $statement = $this->db->prepare("SELECT `Name`, `Color` FROM `Character` WHERE `Room` = ? LIMIT 9999 OFFSET $after");
    $statement->execute(array($this->getID()));
    // calculate the secondary color for each and return in modified array
    return array_map(
      function($x) {
        //YIQ algorithm modified from:
        // http://24ways.org/2010/calculating-color-contrast/
        $prec = floor(strlen($x['Color']) / 3);
        $mult = $prec == 1 ? 17: 1;
        $r = hexdec(substr($x['Color'],1+$prec*0,$prec))*$mult;
        $g = hexdec(substr($x['Color'],1+$prec*1,$prec))*$mult;
        $b = hexdec(substr($x['Color'],1+$prec*2,$prec))*$mult;
        $yiq = (($r*299)+($g*587)+($b*114))/1000;
        return array(
          'Name' => $x['Name'],
          'Color' => $x['Color'],
          'Contrast' => ($yiq >= 128) ? 'black' : 'white'
        );
      },
      $statement->fetchAll()
    );
  }
  
  public function getNumPages() {
    global $rpPostsPerPage;
    return ceil($this->getMessageCount() / $rpPostsPerPage);
  }
  
  public function getStatsArray() {
    $dataStatement = $this->db->prepare("SELECT
      MAX(`Timestamp`) AS `LatestMessageDate`,
      MIN(`Timestamp`) AS `FirstMessageDate`,
      SUM(if(`Type`='Narrator', 1,0)) AS `NarratorMessageCount`,
      SUM(if(`Type`='OOC', 1,0)) AS `OOCMessageCount`,
      SUM(if(`Type`='Narrator', char_length(`Content`),0)) AS `NarratorCharCount`,
      SUM(if(`Type`='Character', char_length(`Content`),0)) AS `CharacterCharCount`,
      SUM(if(`Type`='OOC', char_length(`Content`),0)) AS `OOCCharCount`
      
      FROM `Message`
      WHERE `Room` = ?"
    );
    $dataStatement->execute(array($this->getID()));
    $data = $dataStatement->fetch();
    $top5Statement = $this->db->prepare("SELECT `Character_Name` AS `Name`, COUNT(*) AS `MessageCount` FROM `Message` WHERE `Type`='Character' AND `Room` = ? GROUP BY `Character_Name` ORDER BY `MessageCount` DESC LIMIT 5;");
    $top5Statement->execute(array($this->getID()));
    return array(
      'MessageCount' => $this->getMessageCount(), 'CharacterCount' => $this->getCharacterCount(),
      'FirstMessageDate' => $data['FirstMessageDate'],
      'LatestMessageDate' => $data['LatestMessageDate'],
      'NarratorMessageCount' => $data['NarratorMessageCount'],
      'CharacterMessageCount' => $this->getMessageCount() - $data['NarratorMessageCount'] - $data['OOCMessageCount'],
      'OOCMessageCount' => $data['OOCMessageCount'],
      'NarratorCharCount' => $data['NarratorCharCount'],
      'CharacterCharCount' => $data['CharacterCharCount'],
      'OOCCharCount' => $data['OOCCharCount'],
      'TotalCharCount' => $data['NarratorCharCount'] + $data['CharacterCharCount'] + $data['OOCCharCount'],
      'TopCharacters' => $top5Statement->fetchAll()
    );
  }
  
  public function addMessage($type, $content, $character = null) {
    if(!in_array($type, array('Narrator', 'Character', 'OOC'))) {
      throw new Exception('Invalid type: ' . $type);
    }
    $content = trim($content);
    if(!$content) {
      throw new Exception('Message is empty.');
    }
    $statement = $this->db->prepare("INSERT INTO `Message` (`Type`, `Content`, `Room`, `Character_Name`, `IP`) VALUES (?, ?, ?, ?, ?)");
    $statement->execute(array($type, $content, $this->getID(), $character, $_SERVER['REMOTE_ADDR']));
  }
  
  public function addCharacter($name, $color) {
    $name = trim($name);
    if(!$name) {
      throw new Exception('Name is empty.');
    }
    if(!preg_match_all('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
      throw new Exception("$color is not a valid hex color.");
    }
    $statement = $this->db->prepare("INSERT INTO `Character` (`Name`, `Room`, `Color`, `IP`) VALUES (?, ?, ?, ?)");
    $statement->execute(array($name, $this->getID(), $color, $_SERVER['REMOTE_ADDR']));
  }
}

?>