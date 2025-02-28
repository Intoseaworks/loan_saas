<?php

$router->group([
    'middleware' => ['auth.services'],
    'prefix' => 'api/risk',
], function ($router) {
    require 'data/send_data.php';
    require 'task/task.php';
});

$router->group([
], function ($router) {
});
