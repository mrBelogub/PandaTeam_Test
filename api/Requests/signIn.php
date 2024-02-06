<?php

// Запит для авторизації

// Отримуємо дані користувача
$email = $_POST["email"] ?? null;
$password = $_POST["password"] ?? null;

// Перевіряємо, чи не пусті ці дані
Validator::isEmpty("E-mail", $email);
Validator::isEmpty("Пароль", $password);

// Перевірямо, чи валідний емейл
Validator::isEmailCorrect($email);

// Авторизуємо користувача
User::signIn($email, $password);