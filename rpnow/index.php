<?php
// Require source files
require_once 'config.php';
require_once 'Room.php';
require_once 'lib/Slim/Slim/Slim.php';

// Autoloader gets some other files
\Slim\Slim::registerAutoloader();

// But some stuff isn't loaded automatically...
require_once 'lib/Slim/Slim/View.php';
require_once 'lib/Slim-Views/Twig.php';
require_once 'lib/Slim/Slim/Middleware.php';
require_once 'lib/Slim-Basic-Auth/src/HttpBasicAuthentication.php';
require_once 'lib/Slim-Basic-Auth/src/HttpBasicAuthentication/AuthenticatorInterface.php';
require_once 'lib/Slim-Basic-Auth/src/HttpBasicAuthentication/ArrayAuthenticator.php';require_once 'lib/Slim-Basic-Auth/src/HttpBasicAuthentication/RuleInterface.php';
require_once 'lib/Slim-Basic-Auth/src/HttpBasicAuthentication/RequestMethodRule.php';
require_once 'lib/Slim-Basic-Auth/src/HttpBasicAuthentication/RequestPathRule.php';
require_once 'lib/Twig/lib/Twig/Autoloader.php';

// Create the application
$app = new \Slim\Slim(array(
  'view' => new \Slim\Views\Twig(),
  'debug' => false
));

// Routes are specified in another file
require 'routes.php';

// Run
$app->run();

?>