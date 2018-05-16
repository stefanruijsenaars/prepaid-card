<?php

require __DIR__ . '/vendor/jacwright/restserver/source/Jacwright/RestServer/RestServer.php';
require 'TestController.php';

$server = new \Jacwright\RestServer\RestServer('debug');
$server->useCors = true;
// $server->allowedOrigin = 'http://example.com';
$server->allowedOrigin = '*';

$server->addClass('PrepaidCardController');

$server->handle();
