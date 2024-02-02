<?php

$email = $_GET["email"] ?? null;
$code = $_GET["code"] ?? null;

Validator::isEmpty("E-mail", $email);
Validator::isEmpty("Код", $code);

Validator::isEmailCorrect($email);

User::isUnconfirmed($email, $code);
User::confirm($email, $code);