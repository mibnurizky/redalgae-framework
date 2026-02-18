<?php

$router->get('/', function () {
    echo '<h1>Selamat Datang!</h1>';
});

$router->get('/welcome', [RedAlgae\Controllers\WelcomeController::class,'index']);
$router->get('/welcome/{id}', [RedAlgae\Controllers\WelcomeController::class,'test'])->name('welcome.test');