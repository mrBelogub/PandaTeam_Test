<?php

$user_id = User::getID();

$subscriptions = Subscription::getByUser($user_id);

echo json_encode($subscriptions);