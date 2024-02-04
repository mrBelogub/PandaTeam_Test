<?php

$email = $_GET["email"] ?? null;
$code = $_GET["code"] ?? null;

Validator::isEmpty("E-mail", $email);
Validator::isEmpty("Код", $code);

Validator::isEmailCorrect($email);

User::isNotActivated($email, $code);
User::activate($email, $code);

header("Location: ../index.php"); // Звісно тут треба було б видавати відповідь, яку зрозуміе фронт (типу status = 0), але для демо я вирішив одразу переадресовувати
exit();