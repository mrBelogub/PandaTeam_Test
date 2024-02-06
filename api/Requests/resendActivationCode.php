<?php

// Запит для повторної відправки коду активації

// Отримуємо дані авторизованого користувача
$user_data = User::getData();
// Отримуємо E-mail користувача
$email = $user_data["email"];

// Відправляємо новий код активації
User::sendActivationCode($email, true);