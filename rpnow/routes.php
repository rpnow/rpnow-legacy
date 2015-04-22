<?php
// All room ID's must be alphanumeric and N characters
\Slim\Route::setDefaultConditions(array(
  'id' => '[a-zA-Z0-9]{'.$rpIDLength.','.$rpIDLength.'}'
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
$app->get('/sample/ajax/1', function () use ($app) {
  $data = array(
    'characters' => array(
      array('Name' => 'Cool Character', 'Color' => '#7ad7e7', 'Contrast' => 'black'),
      array('Name' => 'Dog', 'Color' => '#dcc6a7', 'Contrast' => 'black'),
      array('Name' => '<BAD ROBOT>', 'Color' => '#620000', 'Contrast' => 'white'),
      array('Name' => 'Green Rat', 'Color' => '#88be18', 'Contrast' => 'black'),
      array('Name' => 'Some Nerd', 'Color' => '#e69e1a', 'Contrast' => 'black')
    ),
    'messages' => array(
      array('Name' => 'Narrator', 'Content' =>
        "RP Now is a place where you can roleplay with your friends!",
      ),
      array('Name' => 'Narrator', 'Content' =>
        "Creating an RP here takes seconds - give it a title and you're ready to go! There's no account registration process, and it's free!",
      ),
      array('Name' => 'Cool Character', 'Content' =>
        "If you want, you can give your characters their own dialogue boxes!",
      ),
      array('Name' => 'Narrator', 'Content' =>
        "Cool Character's trusty Dog confidently enters the scene.",
      ),
      array('Name' => 'Dog', 'Content' =>
        "Arf! (You can pick the color for each character!)",
      ),
      array('Name' => 'Cool Character', 'Content' =>
        "Haha!",
      ),
      array('Name' => '<BAD ROBOT>', 'Content' =>
        "CREATE AS MANY CHARACTERS AS YOU WANT",
      ),
      array('Name' => 'Narrator', 'Content' =>
        "A stray cat joins the scene. \"However,\" he explains, \"You don't have to use the dialogue boxes if you don't want to. Writing paragraphs in the Narrator voice works just as well.\"",
      ),
      array('Name' => 'Narrator', 'Content' =>
        "Cool Character and Dog go off to fight the Bad Robot. A long and heartwrenching story unfolds. Meanwhile, the stray cat breaks into their house and eats their food, with the help of a green rat.",
      ),
      array('Name' => 'Green Rat', 'Content' =>
        "Once you make an RP, it creates a link you can send to your friends. Then you can all play together in real time.",
      ),
      array('Name' => 'Green Rat', 'Content' =>
        "Even if you all close your browser windows, you can pick up where you left off just by revisiting the link. We won't throw your stories away!",
      ),
      array('Name' => 'Green Rat', 'Content' =>
        "You can also browse through the entire archive of your posts from beginning to end, or download a text copy!",
      ),
      array('Name' => 'Narrator', 'Content' =>
        "The Stray Cat thanks the Green Rat by eating it. The Cat is sick for three days.",
      ),
      array('Name' => 'Narrator', 'Content' =>
        "Some nerd takes a picture of the tragedy on his smartphone.",
      ),
      array('Name' => 'Some Nerd', 'Content' =>
        "RP Now works on smartphones, too. Roleplay on the go!",
      ),
      array('Name' => 'Narrator', 'Content' =>
        "Thanks for reading! Now go back and make an RP!",
      )
    )
  );
  foreach($data['messages'] as &$m) {
    $d = new DateTime();
    $m['Timestamp'] = $d->getTimestamp();
  }
  $app->response->headers->set('Content-Type', 'application/json');
  echo json_encode($data);
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
$app->get('/about/', function () {
  readfile('templates/about.html');
});

// Admin panel!
if(isset($rpAdminPanelEnabled) && $rpAdminPanelEnabled) {
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