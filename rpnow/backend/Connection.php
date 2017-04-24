<?php

if(!isset($rpVersion)) die();

require_once __DIR__.'/../config.php';

class RPDatabase {
  public static function createConnection($useTransaction = true) {
    global $rpDBServer, $rpDBUser, $rpDBPass, $rpDBName;
    $dsn = 'mysql:host=' . $rpDBServer . ';dbname=' . $rpDBName;
    $myConn = new PDO($dsn, $rpDBUser, $rpDBPass);
    $myConn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    if($useTransaction) {
      $myConn->beginTransaction();
    }
    return $myConn;
  }
  
  public static function closeGracefully(&$conn) {
    if($conn->inTransaction()) $conn->commit();
    $conn = null;
  }
}
?>