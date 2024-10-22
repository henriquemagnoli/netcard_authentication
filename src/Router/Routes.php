<?php

use Slim\App;

use Netcard\Controller\AuthenticationController;

return function(App $app)
{
    $app->post('/api/signIn', AuthenticationController::class . ':signIn');
    $app->post('/api/signUp', AuthenticationController::class . ':signUp');
}

?>