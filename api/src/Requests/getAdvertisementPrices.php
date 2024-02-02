<?php

$advertisement_id = $_GET["id"] ?? null;

Validator::isEmpty("ID обʼяви", $advertisement_id);

$subscription_data = Price::getByAdvertisementID($advertisement_id);

echo json_encode($subscription_data ?? []);