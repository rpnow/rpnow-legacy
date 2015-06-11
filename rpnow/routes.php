<?php

// All room ID's must be alphanumeric and N characters
\Slim\Route::setDefaultConditions(array(
  'id' => '[a-zA-Z0-9]{'.$rpIDLength.'}'
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
  $id = $room->getID();
  $room->close();
  $app->redirect($id);
});

// View room
$app->get('/:id/', function ($id) use ($app) {
  try {
    $room = Room::GetRoom($id);
    global $rpPostsPerPage, $rpRootPath, $rpRefreshMillis;
    $app->view()->setData(array(
      'title' => $room->getTitle(),
      'desc' => $room->getDesc(),
      'room' => $id,
      'fullUrl' => 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}$rpRootPath$id",
      'docroot' => $rpRootPath,
      'refreshMillis' => $rpRefreshMillis
    ));
    $room->close();
    $app->render('room.html');
  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
});


// Archive
$app->get('/:id/:page/', function ($id, $page) use ($app) {
  try {
    global $rpRootPath, $rpPostsPerPage;
    $room = Room::GetRoom($id);
    $app->view()->setData(array(
      'title' => $room->getTitle(),
      'desc' => $room->getDesc(),
      'room' => $id,
      'numpages' => $room->getNumPages(),
      'page' => $page,
      'docroot' => $rpRootPath,
      'postsPerPage' => $rpPostsPerPage
    ));
    $room->close();
    $app->render('archive.html');
  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
})->conditions(array('page' => '[1-9][0-9]{0,}'));

// Get archive page data
$app->get('/:id/ajax/:page/', function ($id, $page) use ($app) {
  try {
    $room = Room::GetRoom($id);
    $data = array(
      'messages' => $room->getMessages('page', $page),
      'characters' => $room->getCharacters()
    );
    $room->close();
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode($data);
  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
})->conditions(array('page' => '[1-9][0-9]{0,}'));

// Get latest posts for room
$app->get('/:id/ajax/latest/', function ($id) use ($app) {
  try {
    global $rpPostsPerPage;
    $room = Room::GetRoom($id);
    $data = array(
      'messages' => $room->getMessages('latest'),
      'characters' => $room->getCharacters(),
      'messageCount' => $room->getMessageCount(),
      'characterCount' => $room->getCharacterCount(),
      'postsPerPage' => $rpPostsPerPage
    );
    $room->close();
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode($data);
  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
});

// Receive room updates
$app->get('/:id/ajax/updates/', function ($id) use ($app) {
  try {
    $room = Room::GetRoom($id);
    $data = array(
      'messages' => $room->getMessages('after', $app->request()->get('messages')),
      'characters' => $room->getCharacters($app->request()->get('characters'))
    );
    $room->close();
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode($data);
  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
});

// Send message to room
$app->post('/:id/ajax/message/', function ($id) use ($app) {
  try {
    $room = Room::GetRoom($id);
    if($app->request()->post('type') == 'Character') {
      $room->addMessage(
        'Character',
        $app->request()->post('content'),
        $app->request()->post('name')
      );
    }
    else {
      $room->addMessage(
        $app->request()->post('type'),
        $app->request()->post('content')
      );
    }
    $room->close();
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode(array('status'=>'OK'));
  }
  catch(Exception $e) {
    $app->response->setStatus(500);
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode(array('status'=>'ERROR', 'message'=>$e->getMessage()));
  }
});

// Add character to room
$app->post('/:id/ajax/character/', function ($id) use ($app) {
  try {
    $room = Room::GetRoom($id);
    $room->addCharacter(
      $app->request()->post('name'),
      $app->request()->post('color')
    );
    $room->close();
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode(array('status'=>'OK'));
  }
  catch(Exception $e) {
    $app->response->setStatus(500);
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode(array('status'=>'ERROR', 'message'=>$e->getMessage()));
  }
});

// Sample room!
$app->get('/sample/', function () use ($app) {
  global $rpRootPath;
  $app->view()->setData(array(
    'title' => 'Sample Roleplay',
    'desc' => 'This is what an RP will look like!',
    'room' => 'sample',
    'hidemenu' => true,
    'numpages' => 1,
    'page' => 1,
    'docroot' => $rpRootPath
  ));
  $app->render('archive.html');
});
$app->get('/sample/ajax/1/', function () use ($app) {
  $app->response->headers->set('Content-Type', 'application/json');
  readfile('assets/sample_rp.json');
});

// Generate some statistics for the room
$app->get('/:id/stats/', function ($id) use ($app) {
  try {
    global $rpRootPath;
    $room = Room::GetRoom($id);
    $app->view()->setData(
      array_merge($room->getStatsArray(), array(
        'title' => $room->getTitle(),
        'desc' => $room->getDesc(),
        'room' => $id,
        'docroot' => $rpRootPath
      ))
    );
    $room->close();
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
  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
});

// About
$app->get('/about/', function () use ($app) {
  $app->render('about.html');
});

// Terms
$app->get('/terms/', function () use ($app) {
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
    global $rpRootPath;
    $data = array(
      'rps' => Room::AuditRooms(),
      'docroot' => $rpRootPath
    );
    $app->view()->setData($data);
    $app->render('admin.html');
  });
}

?>