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
      'room' => $room->getID()
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

$app->run();

?>