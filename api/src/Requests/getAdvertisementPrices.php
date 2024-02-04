<?php

$advertisement_id = $_GET["id"] ?? null;

Validator::isEmpty("ID оголошення", $advertisement_id);

$subscription_data = Price::getByAdvertisementForUser($advertisement_id);

echo json_encode($subscription_data ?? []);