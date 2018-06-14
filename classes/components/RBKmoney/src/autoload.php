<?php

spl_autoload_register(function($name) {
    $file = preg_replace('/\\\/', '/', $name);
    $path = dirname(__DIR__) . "/$file.php";

    if (file_exists($path)) {
        include $path;
    }
});