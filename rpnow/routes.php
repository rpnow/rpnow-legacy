<?php

// All room ID's must be alphanumeric and N characters
\Slim\Route::setDefaultConditions(array(
  'id' => '['.preg_quote($rpIDChars).']{'.$rpIDLength.'}'
));

// Maintenance Mode Middleware
$downCheck = function () use ($app) {
  global $rpDown;
  if(isset($rpDown)) {
    $app->response->setStatus(503);
    $app->view()->setData(array('info' => $rpDown));
    $app->render('down.html');
    $app->stop();
  }
};
$downCheckAjax = function () use ($app) {
  global $rpDown;
  if(isset($rpDown)) {
    $app->halt(503);
  }
};

// Error pages
$app->error(function (Exception $e) use ($app) {
  if($app->response->headers->get('Content-Type') == 'application/json') {
    $app->response->setStatus(500);
    echo json_encode(array('error'=>$e->getMessage()));
  }
  else if($e->getCode() == Room::ROOM_NOT_FOUND_EXCEPTION) {
    $app->response->setStatus(404);
    $id = $app->request->getResourceUri();
    $id = substr($id, 1);
    if(strpos($id, '/')) $id = substr($id, 0, strpos($id, '/'));
    $app->view()->setData(array(
      'docroot'=> $app->request->getRootUri() . '/',
      'room'=> $id
    ));
    $app->render('404rp.html');
  }
  else {
    $app->response->setStatus(500);
    $app->view()->setData(array(
      'docroot'=> $app->request->getRootUri() . '/',
      'uri'=> $app->request->getResourceUri(),
      'message'=> $e->getMessage()
    ));
    $app->render('5xx.html');
  }
});

// Home page
$app->get('/', $downCheck, function () {
  readfile('templates/home.html');
});

// Create room
$app->post('/create/', $downCheck, function () use ($app) {
  $room = Room::CreateRoom(
    $app->request()->post('title'),
    $app->request()->post('desc')
  );
  $id = $room->getID();
  $room->close();
  $app->redirect($id);
});

// View room
$app->get('/:id/', $downCheck, function ($id) use ($app) {
  $room = Room::GetRoom($id);
  $app->view()->setData(array(
    'room' => $id,
    'title' => $room->getTitle(),
    'desc' => $room->getDesc(),
    'docroot' => './'
  ));
  $room->close();
  $app->render('room.html');
});


// Archive
$app->get('/:id/:page/', $downCheck, function ($id, $page) use ($app) {
  $room = Room::GetRoom($id);
  if($page > $room->getNumPages() && $page > 1) {
    throw new Exception("Page $page does not yet exist.");
  }
  $app->view()->setData(array(
    'room' => $id,
    'title' => $room->getTitle(),
    'desc' => $room->getDesc(),
    'docroot' => '../',
    'page' => $page,
    'numpages' => $room->getNumPages()
  ));
  $room->close();
  $app->render('archive.html');
})->conditions(array('page' => '[1-9][0-9]{0,}'));

// Get archive page data
$app->get('/:id/ajax/page/:page/', $downCheckAjax, function ($id, $page) use ($app) {
  $app->response->headers->set('Content-Type', 'application/json');
  $room = Room::GetRoom($id);
  $data = array(
    'msgs' => $room->getMessages('page', $page),
    'charas' => $room->getCharacters(),
    'numpages' => $room->getNumPages()
  );
  $room->close();
  echo json_encode($data);
})->conditions(array('page' => '[1-9][0-9]{0,}'));

// Get latest posts for room
$app->get('/:id/ajax/chat/', $downCheckAjax, function ($id) use ($app) {
  $app->response->headers->set('Content-Type', 'application/json');
  global $rpPostsPerPage, $rpRefreshMillis;
  $room = Room::GetRoom($id);
  $data = array(
    'msgs' => $room->getMessages('latest'),
    'charas' => $room->getCharacters(),
    'msgCounter' => $room->getMessageCount(),
    'charaCounter' => $room->getCharacterCount(),
    'upMsgCounter' => 0,
    'upCharaCounter' => 0,
    'postsPerPage' => $rpPostsPerPage,
    'refreshMillis' => $rpRefreshMillis
  );
  $room->close();
  echo json_encode($data);
});

// Receive room updates
function echoRoomUpdates($room, $app) {
  $msgs = null;
  $charas = null;
  if($app->request->isGet()) {
    $msgs = $room->getMessages('after', $app->request()->get('msgCounter'));
    $charas = $room->getCharacters($app->request()->get('charaCounter'));
  }
  else if($app->request->isPost()) {
    $msgs = $room->getMessages('after', $app->request()->post('msgCounter'));
    $charas = $room->getCharacters($app->request()->post('charaCounter'));
  }
  
  $data = array();
  if(count($msgs) != 0) $data['newMsgs'] = $msgs;
  if(count($charas) != 0) $data['newCharas'] = $charas;
  
  echo json_encode($data);
}
$app->get('/:id/ajax/updates/', $downCheckAjax, function ($id) use ($app) {
  $app->response->headers->set('Content-Type', 'application/json');
  $room = Room::GetRoom($id);
  echoRoomUpdates($room, $app);
  $room->close();
});

// Send message to room
$app->post('/:id/ajax/message/', $downCheckAjax, function ($id) use ($app) {
  $app->response->headers->set('Content-Type', 'application/json');
  $room = Room::GetRoom($id);
  if($app->request()->post('type') == 'Character') {
    $room->addMessage(
      'Character',
      $app->request()->post('content'),
      $app->request()->post('charaId')
    );
  }
  else {
    $room->addMessage(
      $app->request()->post('type'),
      $app->request()->post('content')
    );
  }
  echoRoomUpdates($room, $app);
  $room->close();
});

// Add character to room
$app->post('/:id/ajax/character/', $downCheckAjax, function ($id) use ($app) {
  $app->response->headers->set('Content-Type', 'application/json');
  $room = Room::GetRoom($id);
  $room->addCharacter(
    $app->request()->post('name'),
    $app->request()->post('color')
  );
  echoRoomUpdates($room, $app);
  $room->close();
});

// Sample room!
$app->get('/sample/', $downCheck, function () use ($app) {
  $app->view()->setData(array(
    'title' => 'Sample Roleplay',
    'desc' => 'This is what an RP will look like!',
    'room' => 'sample',
    'hidemenu' => true,
    'numpages' => 1,
    'page' => 1,
    'docroot' => ''
  ));
  $app->render('archive.html');
});
$app->get('/sample/ajax/page/1/', function () use ($app) {
  $app->response->headers->set('Content-Type', 'application/json');
  readfile('assets/sample_rp.json');
});

// Generate some statistics for the room
$app->get('/:id/stats/', $downCheck, function ($id) use ($app) {
  $room = Room::GetRoom($id);
  $app->view()->setData(
    array_merge($room->getStatsArray(), array(
      'title' => $room->getTitle(),
      'desc' => $room->getDesc(),
      'room' => $id,
      'docroot' => '../'
    ))
  );
  $room->close();
  $app->render('stats.html');
});

// Export room to txt file
$app->get('/:id/export/', $downCheck, function ($id) use ($app) {
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
  foreach($room->getTranscript() as $message) {
    if($message['Type'] == 'Character') {
      echo strtoupper($message['Name']) . ":\r\n";
      echo '  ' . str_replace("\n", "\r\n  ", wordwrap($message['Content'], 70, "\n"));
    }
    else if($message['Type'] == 'OOC') {
      echo str_replace("\n", "\r\n", wordwrap('(( OOC: ' . $message['Content'] . ' ))', 72, "\n"));
    }
    else {
      echo str_replace("\n", "\r\n", wordwrap($message['Content'], 72, "\n"));
    }
    
    echo "\r\n\r\n";
  }
  $room->close();
});

// About
$app->get('/about/', $downCheck, function () use ($app) {
  $app->render('about.html');
});

// Terms
$app->get('/terms/', $downCheck, function () use ($app) {
  $app->render('terms.html');
});

// Admin panel!
if(isset($rpAdminPanelEnabled) && $rpAdminPanelEnabled) {
  $app->add(new \Slim\Middleware\HttpBasicAuthentication(array(
    'path' => '/admin/',
    'realm' => 'RPNow Admin Panel',
    'users' => array(
      $rpAdminPanelUser => $rpAdminPanelPass
    )
  )));
  $app->get('/admin/', function () use ($app) {
    $data = array(
      'rps' => Room::AuditRooms(),
      'docroot' => ''
    );
    $app->view()->setData($data);
    $app->render('admin.html');
  });
}

// MOTD
$app->get('/broadcast/', function () use ($app) {
  global $rpDown;
  if(isset($rpDown)) {
    echo $rpDown;
    $app->stop();
  }
  global $rpBroadcast;
  if(isset($rpBroadcast)) echo $rpBroadcast;
});

?>