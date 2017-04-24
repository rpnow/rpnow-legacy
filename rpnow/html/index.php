<?php
// RPNow Version Number
$rpVersion = '1.1.0';

// Require source files
require '../vendor/autoload.php';
\Slim\Slim::registerAutoloader();

require_once '../backend/Room.php';

// Create the application
$app = new \Slim\Slim(array(
  'view' => new \Slim\Views\Twig(),
  'debug' => true,
  'templates.path' => '../templates'
));
require '../backend/routes.php';

// Run
$app->run();

?>