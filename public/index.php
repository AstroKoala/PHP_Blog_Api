<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../src/config/db.php'; // for database configuration

$app = new \Slim\App;
// can put routes here as below

// $app->get('/hello/{name}', function (Request $request, Response $response) {
//      $name = $request->getAttribute('name');
//      $response->getBody()->write("<h1>Hello, $name</h1>");
//
//      return $response;
// });

// or can put in seperate file as below

require '../src/routes.php';

$app->run();
?>
