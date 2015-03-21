<?php
require_once 'config.php';
require_once 'Room.php';
require_once 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(array(
  'view' => new \Slim\Views\Twig()
));

// Home page
$app->get('/', function () {
  readfile('home.html');
});

// View room
$app->get('/:id', function ($id) use ($app) {
  try {
    $room = Room::GetRoom($id);
    $app->view()->setData(array(
      'title' => $room->getTitle(),
      'desc' => $room->getDesc(),
      'room' => $room->getID(),
      'messages' => $room->getMessages(),
      'characters' => $room->getCharacters()
    ));
    $app->render('room.html');
  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
});

// Create room
$app->post('/create', function () use ($app) {
  $room = Room::CreateRoom(
    $app->request()->post('title'),
    $app->request()->post('desc')
  );
  $app->redirect($room->getID());
});

// Send message to room
$app->post('/:id/send', function ($id) use ($app) {
  try {
    $room = Room::GetRoom($id);
    $room->send(
      $app->request()->post('name'),
      $app->request()->post('content')
    );
    $app->redirect('/'.$room->getID());
  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
});

// Add character to room
$app->post('/:id/character', function ($id) use ($app) {
  try {
    $room = Room::GetRoom($id);
    $room->addCharacter(
      $app->request()->post('name'),
      $app->request()->post('color')
    );
    $app->redirect('/'.$room->getID());
  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
});

// Generate some statistics for the room
$app->get('/:id/stats', function ($id) use ($app) {
  try {
    $room = Room::GetRoom($id);
    $app->view()->setData(
      array_merge($room->getStatsArray(), array(
        'title' => $room->getTitle(),
        'desc' => $room->getDesc(),
        'room' => $room->getID()
      ))
    );
    $app->render('stats.html');
  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
});

// Export room to txt file
$app->get('/:id/export', function ($id) use ($app) {
  try {
    $room = Room::GetRoom($id);
    // .txt download response headers
    $app->response->headers->set('Content-Type', 'text/plain');
    $app->response->headers->set('Content-disposition', 'attachment; filename="'.$room->getTitle().'.txt"');
    // output text
    // generate title text
    echo strtoupper($room->getTitle()) . "\r\n";
    echo wordwrap($room->getDesc(), 72, "\r\n") . "\r\n";
    echo "--------\r\n\r\n";
    // output each message
    foreach($room->getMessages() as $message) {
      if($message['Name'] != 'Narrator') {
        echo strtoupper($message['Name']) . ":\r\n";
        echo '  ' . str_replace("\n", "\r\n  ", wordwrap($message['Content'], 70, "\n"));
      }
      else {
        echo str_replace("\n", "\r\n", wordwrap($message['Content'], 72, "\n"));
      }
      
      echo "\r\n\r\n";
    }
  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
});

$app->run();

?>