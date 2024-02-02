<?php

$user_id = User::getID();

$url = $_POST["url"] ?? null;

Validator::isEmpty("Посилання", $url);

Validator::isURLCorrect($url);

$advertisement = new Advertisement($url);

Subscription::create($user_id, $advertisement);

