<?php

#implementazione sistema di routing;
$request = $_SERVER['REQUEST_URI'];
//var_dump($request);
$router = str_replace('/views/', '/', $request);


if($router == '/' || str_contains($router, '/home')){
    include __DIR__ . '/.././views/home.html';
}elseif(str_contains($router, 'login')){
    include __DIR__ . '/.././views/login.html';
} elseif(str_contains($router, 'admin')){
    include __DIR__ . '/.././views/admin.html';
}elseif(str_contains($router, 'logout')){
    include __DIR__ . '/.././views/logout.html';
}elseif(str_contains($router, 'validation')){
    include __DIR__ . '/.././views/validation.html';
}elseif(str_contains($router, '404')){
    include __DIR__ . '/.././views/404.html';
}

?>