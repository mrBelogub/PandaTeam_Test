<?php

$email = $_POST["email"] ?? null;
$password = $_POST["password"] ?? null;

Validator::isEmpty("E-mail", $email);
Validator::isEmpty("Пароль", $password);

Validator::isEmailCorrect($email);

$user_data = User::getDataByEmail($email);

if(!empty($user_data)){
    throw new Exception("Користувач з вказаним E-mail вже існує!");
}

User::signUp($email, $password);

User::sendActivationCode($email);