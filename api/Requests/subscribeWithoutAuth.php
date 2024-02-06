<?php

// Запит для нової підписки на оголошення без реєстрації/авторизації

// Отримуємо дані
$url = $_POST["url"];
$email = $_POST["email"];

// Перевіряємо, чи не пусті дані
Validator::isEmpty("Посилання", $url);
Validator::isEmpty("E-mail", $email);

// Перевіряємо, чи валідні дані
Validator::isURLCorrect($url);
Validator::isEmailCorrect($email);

// Створюємо аккаунт за користувача
$user_id = User::createFromSubscriptionForm($email);

// Зберігаємо нове оголошення (або отримуємо, якщо вже існує)
$advertisement = new Advertisement($url);
// Підписуємо користувача на оголошення
Subscription::create($user_id, $advertisement);