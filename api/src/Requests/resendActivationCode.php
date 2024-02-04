<?php

$user_data = User::getData();
$email = $user_data["email"];

User::sendActivationCode($email, true);