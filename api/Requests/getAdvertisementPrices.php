<?php

// Запит для отримування ціни конкретного оголошення

// Отримуємо ID оголошення
$advertisement_id = $_GET["id"] ?? null;

// Перевіряємо: що ID оголошення не пусте
Validator::isEmpty("ID оголошення", $advertisement_id);

// Отримуємо ціни оголошення
$subscription_data = Price::getByAdvertisementForUser($advertisement_id);

// Повертаємо ії на фронт
echo json_encode($subscription_data);