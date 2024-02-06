<?php

// Запит для отримування усіх підписок користувача

// Отримуємо інформацю про авторизованого користувача
$user_data = User::getData();

// Отримуємо ID користувача
$user_id = $user_data["id"];

// Отримуємо інформацію про те чи активований в нього профіль
$is_not_activated = boolval($user_data["activation_code"]);

// Отримуємо всі підписки користувача
$subscriptions = Subscription::getAllByUser($user_id);

// Формуємо массив для відповіді
$result = [
    "subscriptions" => $subscriptions,
    "is_not_activated" => $is_not_activated
];

// Відправляємо інформацію на фронт
echo json_encode($result);