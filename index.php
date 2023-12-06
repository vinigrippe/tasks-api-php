<?php
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$pdo = require __DIR__ . '/database/db.php';
require __DIR__ . '/routes/login.php';

$app = AppFactory::create();

loginRoute($app, $pdo);

$app->run();