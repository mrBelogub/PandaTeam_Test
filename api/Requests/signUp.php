<?php

// Запит для реєстрації

// Отримуємо дані користувача
$email = $_POST["email"] ?? null;
$password = $_POST["password"] ?? null;

// Перевіряємо, чи дані не пусті
Validator::isEmpty("E-mail", $email);
Validator::isEmpty("Пароль", $password);

// Перевіряємо, чи валідний емейл
Validator::isEmailCorrect($email);

// Перевіряємо чи є вже користувач за таким емейлом
$user_data = User::getDataByEmail($email);

// Якщо вже є - видаємо помилку
if(!empty($user_data)){
    throw new Exception("Користувач з вказаним E-mail вже існує!");
}

// Реєструємо користувача
User::signUp($email, $password);

// Відправляємо йому код активації
User::sendActivationCode($email);