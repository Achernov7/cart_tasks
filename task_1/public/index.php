<?php

require_once '../init.php';
ini_set('display_errors', 1); // убрать

//супер сомнительно.
try {
    ProductController::index( $_GET['group'] ?? null );
} catch (Exception $e) {
    // если 8 версия
    if (str_contains($e->getMessage(), 'Wrong')) {
        View::render('error', ['error' => $e->getMessage()]);
    } else {
        View::render('error', ['error' => 'Unknown error. Ask for help.']);
    }
}
