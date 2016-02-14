<?php

if(!isset($rpVersion)) die();

// globally set some Twig variables
$app->view()->setData(array(
  'docroot'=> $app->request->getRootUri() . '/',
  'version' => $rpVersion
));

// All room ID's must be alphanumeric and N characters
\Slim\Route::setDefaultConditions(array(
  'id' => '['.preg_quote($rpIDChars).']{'.$rpIDLength.'}'
));

// numeric routes use this regex
$numericRouteCondition = '[1-9][0-9]{0,}';

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
$app->notFound(function () use ($app) {
  $app->view()->setData(array(
    'uri'=> $app->request->getResourceUri()
  ));
  $app->render('404.html');
});
$app->error(function (Exception $e) use ($app) {
  if($app->response->headers->get('Content-Type') == 'application/json') {
    $app->response->setStatus(500);
    echo json_encode(array('error'=>$e->getMessage()));
  }
  else if($e->getCode() == Room::ROOM_NOT_FOUND_EXCEPTION) {
    $app->response->setStatus(404);
    $id = $app->request->getResourceUri();
    $id = substr($id, 4);
    if(strpos($id, '/')) $id = substr($id, 0, strpos($id, '/'));
    $app->view()->setData(array(
      'room'=> $id
    ));
    $app->render('404rp.html');
  }
  else {
    $app->response->setStatus(500);
    $app->view()->setData(array(
      'uri'=> $app->request->getResourceUri(),
      'message'=> $e->getMessage()
    ));
    $app->render('5xx.html');
  }
});

// Home page
$app->get('/', $downCheck, function () use ($app) {
  $app->render('home.html');
});

// About
$app->get('/about/', $downCheck, function () use ($app) {
  $app->render('about.html');
});

// Terms
$app->get('/terms/', $downCheck, function () use ($app) {
  $app->render('terms.html');
});

// Formatting info
$app->get('/format/', $downCheck, function () use ($app) {
  $app->render('format.html');
});

// Create room
$app->post('/create/', $downCheck, function () use ($app) {
  $roomId = Room::CreateRoom(
    $app->request()->post('title'),
    $app->request()->post('desc')
  );
  $app->redirect('rp/' . $roomId);
});

// RP Pages
$app->group('/rp', $downCheck, function() use ($app) {
  global $numericRouteCondition;
  
  // Main room chat
  $app->get('/:id/', function ($id) use ($app) {
    $room = Room::GetRoom($id);
    $app->view()->setData(array(
      'room' => $id,
      'title' => $room->getTitle(),
      'desc' => $room->getDesc()
    ));
    $room->close();
    $app->render('room.html');
  });
  
  // Archive pages
  $app->get('/:id/:page/', function ($id, $page) use ($app) {
    $room = Room::GetRoom($id);
    if($page > $room->getNumPages() && $page > 1) {
      throw new Exception("Page $page does not yet exist.");
    }
    $app->view()->setData(array(
      'room' => $id,
      'title' => $room->getTitle(),
      'desc' => $room->getDesc(),
      'page' => $page,
      'numpages' => $room->getNumPages()
    ));
    $room->close();
    $app->render('archive.html');
  })->conditions(array('page' => $numericRouteCondition));
  
  // Generate some statistics for the room
  $app->get('/:id/stats/', function ($id) use ($app) {
    $room = Room::GetRoom($id);
    $app->view()->setData(
      array_merge($room->getStatsArray(), array(
        'title' => $room->getTitle(),
        'desc' => $room->getDesc(),
        'room' => $id,
      ))
    );
    $room->close();
    $app->render('stats.html');
  });
  
  // Export room to txt file
  $app->get('/:id/export/', function ($id) use ($app) {
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

});

// API
$app->group('/api', $downCheckAjax, function() use ($app) {
  global $numericRouteCondition;
  
  // Get archive page data
  $app->get('/archive/', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $id = $app->request->get('id');
    $room = Room::GetRoom($id);
    $data = array(
      'msgs' => $room->getMessages('page', $app->request->get('page')),
      'charas' => $room->getCharacters(),
      'numpages' => $room->getNumPages()
    );
    $room->close();
    echo json_encode($data);
  })->conditions(array('page' => $numericRouteCondition));
  
  // Get latest posts for room
  $app->get('/chat/', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    global $rpPostsPerPage, $rpRefreshMillis;
    $id = $app->request->get('id');
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
  $app->get('/updates/', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $id = $app->request->get('id');
    $room = Room::GetRoom($id);
    echoRoomUpdates($room, $app);
    $room->close();
  });
  
  // Send message to room
  $app->post('/message/', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $id = $app->request->post('id');
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
  $app->post('/character/', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $id = $app->request->post('id');
    $room = Room::GetRoom($id);
    $room->addCharacter(
      $app->request()->post('name'),
      $app->request()->post('color')
    );
    echoRoomUpdates($room, $app);
    $room->close();
  });

});

// Sample room!
$app->get('/sample/', $downCheck, function () use ($app) {
  $app->view()->setData(array(
    'title' => 'Sample Roleplay',
    'desc' => 'This is what an RP will look like!',
    'sample' => true,
    'numpages' => 1,
    'page' => 1
  ));
  $app->render('archive.html');
});
$app->get('/sample/archive/', function () use ($app) {
  $app->response->headers->set('Content-Type', 'application/json');
  readfile('assets/sample_rp.json');
});

// Admin panel!
if(isset($rpAdminCredentials)) {
  require_once 'backend/Admin.php';
  
  $app->add(new \Slim\Middleware\HttpBasicAuthentication(array(
    'path' => '/admin/',
    'realm' => 'RPNow Admin Panel',
    'users' => $rpAdminCredentials
  )));
  
  $app->group('/admin', function() use ($app) {
    global $numericRouteCondition;
    
    // admin home
    $app->get('/', function () use ($app) {
      $app->render('admin/dash.html');
    });
    
    // RPs that were most recently active
    $app->get('/activity(/:num)/', function ($num = 30) use ($app) {
      $rps = Admin::RecentActivity($num);
      $app->view()->setData(array(
        'title' => 'Recent Activity',
        'description' => 'Showing the ' . count($rps) . ' most recently active RPs.',
        'rps' => $rps
      ));
      $app->render('admin/rptable.html');
    })->conditions(array('num' => $numericRouteCondition));
    
    // RPs ordered by most recently created
    $app->get('/newest(/:num)/', function ($num = 30) use ($app) {
      $rps = Admin::NewestRooms($num);
      $app->view()->setData(array(
        'title' => 'Newest RPs',
        'description' => 'Showing the ' . count($rps) . ' most recently created RPs.',
        'rps' => $rps
      ));
      $app->render('admin/rptable.html');
    })->conditions(array('num' => $numericRouteCondition));
    
    // top rps in the last (hour, day, week, month, all-time)
    $app->get('/top(/:scale(/:num))/', function ($scale = 'day', $num = 30) use ($app) {
      // get relevent RPs
      $rps = null;
      $description = null;
      // all-time top...
      if($scale == 'all-time') {
        $rps = Admin::AllTimeTopRPs($num);
        $description = "Showing the " . count($rps) . " RPs with the all-time highest number of posts.";
      }
      // ...or top within certain scale
      else {
        $hour = 60 * 60;
        $scales = array(
          'hour' => 1 * $hour,
          'day' => 24 * $hour,
          'week' => 7 * 24 * $hour,
          'month' => 28 * 24 * $hour
        );
        if(!isset($scales[$scale])) throw new Exception('Invalid timescale.');
        $secs = $scales[$scale];
        $rps = Admin::TopRPs($secs, $num);
        $description = "Showing the top " . count($rps) . " RPs with the most posts in the last $scale.";
      }
      // render
      $app->view()->setData(array(
        'title' => "Top RPs ($scale)",
        'description' => $description,
        'rps' => $rps
      ));
      $app->render('admin/rptable.html');
    });
    
    // RPs with the most time between their start and their most recent post
    $app->get('/duration(/:num)/', function ($num = 30) use ($app) {
      $rps = Admin::LongestDuration($num);
      $app->view()->setData(array(
        'title' => 'Longest Duration RPs',
        'description' => 'Showing the ' . count($rps) . ' RPs with the longest amount of time between the first and last post.',
        'rps' => $rps
      ));
      $app->render('admin/rptable.html');
    })->conditions(array('num' => $numericRouteCondition));
    
    // streams messages from all RPs into one channel
    $app->get('/activity-stream/', function () use ($app) {
      echo "Stream of all messages from all RPs";
    });
    
    // search for keywords in titles, or in fulltext
    $app->get('/search/:type/:keyword/', function ($type, $keyword) use ($app) {
      // convert '+' back to a space and decode other uri elements
      $keyword = urldecode($keyword);
      // search
      $rps = null;
      if($type == 'title') {
        $rps = Admin::SearchTitles($keyword, 30);
        $description = 'Showing most recent ' . count($rps) . ' RPs with "' . $keyword . '" in the title.';
      }
      else if($type == 'fulltext') {
        $rps = Admin::SearchFull($keyword, 30);
        $description = 'Showing most recent ' . count($rps) . ' RPs where "' . $keyword . '" was sent in some message.';
      }
      else {
        throw new Exception("Unknown search type: $type");
      }
      $app->view()->setData(array(
        'title' => 'Search Results',
        'description' => $description,
        'rps' => $rps
      ));
      $app->render('admin/rptable.html');
    });
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

/*
// MIGRATE MAGIC DONT KEEP THIS FOR LONG
$app->get('/popmigrate/', function() use ($app) {
  Room::PopulateMigrationTable();
  echo ':)';
});
*/

?>