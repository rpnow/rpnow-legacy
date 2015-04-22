<?php
require_once 'config.php';
require_once 'Room.php';
require_once 'lib/Slim/Slim.php';
require_once 'lib/Slim/Middleware.php';
require_once 'lib/Slim/Middleware/HttpBasicAuth.php';
use Slim\Slim;
use Slim\Extras\Middleware\HttpBasicAuth;

Slim::registerAutoloader();

$app = new Slim(array(
  'view' => new \Slim\Views\Twig()
));

// Route-specific authentication
// http://stackoverflow.com/questions/17212753/httpbasicauth-inside-of-a-slim-route
class HttpBasicAuthCustom extends HttpBasicAuth {
  protected $route;

  public function __construct($username, $password, $realm = 'Protected Area', $route = '') {
    $this->route = $route;
    parent::__construct($username, $password, $realm);        
  }

  public function call() {
    if(strpos($this->app->request()->getPathInfo(), $this->route) !== false) {
      parent::call();
      return;
    }
    $this->next->call();
  }
}
if(isset($rpAdminPanelEnabled) && $rpAdminPanelEnabled) {
  $app->add(new HttpBasicAuthCustom($rpAdminPanelUser, $rpAdminPanelPass, 'RPNow Admin Panel', '/admin'));
}

require 'routes.php';

$app->run();

?>