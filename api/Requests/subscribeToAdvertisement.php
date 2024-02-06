<?php

// Запит для створення нової підписки на оголошення

// Отримуємо посилання на оголошення
$url = $_POST["url"] ?? null;

// Перевіряємо, чи не пусте посилання
Validator::isEmpty("Посилання", $url);

// Перевіряємо, чи валідне посилання
Validator::isURLCorrect($url);

// Зберігаємо нове оголошення (або отримуємо, якщо вже існує)
$advertisement = new Advertisement($url);

// Отримуємо ID авторизованого користувача
$user_id = User::getID();
// Підписуємо користувача на оголошення
Subscription::create($user_id, $advertisement);

