<?php

// Розпочинаємо сессію
session_start();

// Отримуємо що там хотів від нас користувач
$action = $_GET['action'] ?? null;

// Якщо action прийшов пустим - видаємо помилку
if(empty($action)) {
    http_response_code(400);
    echo 'Unknown action';
    exit();
}

const ACTION_WITHOUT_AUTH = ["signUp", "signIn", "subscribeWithoutAuth", "activateProfile"];

if (!in_array($action, ACTION_WITHOUT_AUTH)){
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo "401 Unauthorized";
        exit();
    }
}

require_once __DIR__ . "../../db.cfg.php";

// Формуємо шлях до файлу обробника
$file_path = "Requests/" . $action . ".php";

// Якщо в нас нема такого обробника - видаємо помилку
if (!file_exists($file_path)) {
    http_response_code(404);
    echo 'Action not found';
    exit();
}

// Підключаємося до файлу залежностей
require_once "src/Core/requires.php";

try {
    // Намагаємося виконати дію
    require_once $file_path;
} catch (Exception $e) {
    // Якщо чомусь не виходить - видаємо помилку
    $errorMessage = $e->getMessage();
    http_response_code(400);
    echo "Error message: $errorMessage";
    
    exit();
}
