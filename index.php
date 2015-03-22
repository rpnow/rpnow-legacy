<?php
require_once 'config.php';
require_once 'Room.php';
require_once 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();


$app = new \Slim\Slim(array(
  'view' => new \Slim\Views\Twig()
));

// All room ID's must be alphanumeric and N characters
\Slim\Route::setDefaultConditions(array(
  'id' => '[a-zA-Z0-9]{'.$RoomIDLen.','.$RoomIDLen.'}'
));

// Home page
$app->get('/', function () {
  readfile('templates/home.html');
});

// Create room
$app->post('/create/', function () use ($app) {
  $room = Room::CreateRoom(
    $app->request()->post('title'),
    $app->request()->post('desc')
  );
  $app->redirect($room->getID());
});

// View room
$app->get('/:id/', function ($id) use ($app) {
  try {
    $room = Room::GetRoom($id);
    $app->view()->setData(array(
      'title' => $room->getTitle(),
      'desc' => $room->getDesc(),
      'room' => $id,
      'messages' => $room->getMessages('latest'),
      'characters' => $room->getCharacters()
    ));
    $app->render('room.html');
  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
});

// Archive
$app->get('/:id/:page/', function ($id, $page) use ($app) {
  try {
    $room = Room::GetRoom($id);
    $app->view()->setData(array(
      'title' => $room->getTitle(),
      'desc' => $room->getDesc(),
      'room' => $id,
      'messages' => $room->getMessages($page),
      'characters' => $room->getCharacters(),
      'numpages' => $room->getNumPages(),
      'page' => $page
    ));
    $app->render('archive.html');
  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
})->conditions(array('page' => '[1-9][0-9]{0,}'));

// Send message to room
$app->post('/:id/send/', function ($id) use ($app) {
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
$app->post('/:id/character/', function ($id) use ($app) {
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
$app->get('/:id/stats/', function ($id) use ($app) {
  try {
    $room = Room::GetRoom($id);
    $app->view()->setData(
      array_merge($room->getStatsArray(), array(
        'title' => $room->getTitle(),
        'desc' => $room->getDesc(),
        'room' => $id
      ))
    );
    $app->render('stats.html');
  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
});

// Export room to txt file
$app->get('/:id/export/', function ($id) use ($app) {
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
    foreach($room->getMessages('all') as $message) {
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

// About
$app->get('/about/', function () {
  readfile('about.html');
});

$app->run();

?>