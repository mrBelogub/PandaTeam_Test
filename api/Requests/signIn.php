<?php

$email = $_POST["email"] ?? null;
$password = $_POST["password"] ?? null;

Validator::isEmpty("E-mail", $email);
Validator::isEmpty("Пароль", $password);

Validator::isEmailCorrect($email);

User::signIn($email, $password);