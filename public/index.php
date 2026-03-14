<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\ClassLoader;
use App\Controller\ApiController;
use DI\ContainerBuilder;
use Pecee\SimpleRouter\SimpleRouter;

header('Content-Type: application/json');

// Simple routing
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$builder = new ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/../di.php');
$container = $builder->build();

SimpleRouter::setDefaultNamespace('\Api\Controller');
SimpleRouter::setCustomClassLoader(new ClassLoader($container));
SimpleRouter::post('/event/', [ApiController::class, 'handleEvent']);
SimpleRouter::get('/statistics/', [ApiController::class, 'statistics']);

SimpleRouter::start();