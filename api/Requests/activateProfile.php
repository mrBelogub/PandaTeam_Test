<?php

// Запит для активаціїї профілю

// Отримуємо дані користувача
$email = $_GET["email"] ?? null;
$code = $_GET["code"] ?? null;

// Перевірямо, що вони не пусті
Validator::isEmpty("E-mail", $email);
Validator::isEmpty("Код", $code);

// Перевіряємо, чи валідний емейл
Validator::isEmailCorrect($email);

// Перевірямо, що корисутвач досі не активований
// Якщо активований - то посилання вже не дійсне
User::isNotActivated($email, $code);

// Активуємо користувача
User::activate($email, $code);

header("Location: ../index.php"); // Звісно тут треба було б видавати відповідь, яку зрозуміе фронт (типу status = 0), але для демо я вирішив одразу переадресовувати
exit();