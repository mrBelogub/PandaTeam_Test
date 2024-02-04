<?php

$user_data = User::getData();
$user_id = $user_data["id"];

$subscriptions = Subscription::getAllByUser($user_id);

$is_not_activated = boolval($user_data["activation_code"]);

$result = [
    "subscriptions" => $subscriptions,
    "is_not_activated" => $is_not_activated
];

echo json_encode($result);