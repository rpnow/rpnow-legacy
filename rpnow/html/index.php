<?php
// RPNow Version Number
$rpVersion = '0.10.0';

// Require source files
require '../vendor/autoload.php';
\Slim\Slim::registerAutoloader();

require_once '../config.php';
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