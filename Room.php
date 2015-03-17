<?php

require_once 'config.php';

class Room {
  private static $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  
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
    do {
      $id = '';
      for ($i = 0; $i < $RoomIDLen; $i++) {
        $id .= self::$characters[rand(0, strlen(self::$characters) - 1)];
      }
    } while(file_exists("data/$id"));
    mkdir("data/$id");
    $infoFile = fopen("data/$id/info",'w');
    fwrite($infoFile, json_encode(array('title' => $title, 'desc' => $desc)));
    fclose($infoFile);
    return new Room($id, $title, $desc);
  }
  
  public static function GetRoom($id) {
    if(!Room::IsValidID($id)) {
      throw new Exception('Malformed Room ID.');
    }
    if(!file_exists("data/$id")) {
      throw new Exception('Room not found.');
    }
    $file = fopen("data/$id/info", 'r');
    $text = fread($file, filesize("data/$id/info"));
    fclose($file);
    $json = json_decode($text);
    return new Room($id, $json->{'title'}, $json->{'desc'});
  }
  
  public function getID() { return $this->id; }
  public function getTitle() { return $this->title; }
  public function getDesc() { return $this->desc; }
  
  public static function IsValidID($id) {
    global $RoomIDLen;
    return ctype_alnum($id) && strlen($id) == $RoomIDLen;
  }
}

?>