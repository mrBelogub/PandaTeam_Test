<?php

$url = $_POST["url"];
$email = $_POST["email"];

Validator::isEmpty("Посилання", $url);
Validator::isEmpty("E-mail", $email);

Validator::isEmailCorrect($email);

$user_id = User::createFromSubscriptionForm($email);
$advertisement = new Advertisement($url);
Subscription::create($user_id, $advertisement);