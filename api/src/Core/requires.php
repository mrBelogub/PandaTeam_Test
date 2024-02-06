<?php

require_once 'mail.php'; // Відправка листів
require_once 'sql_shortcuts.php'; // Скорочення для SQL запитів
require_once 'validator.php'; // Валідатор

require_once __DIR__.'/../Controllers/Advertisement.php'; // Оголошення
require_once __DIR__.'/../Controllers/OLX.php'; // OLX
require_once __DIR__.'/../Controllers/Price.php'; // Ціни
require_once __DIR__.'/../Controllers/Subscription.php'; // Підписки 
require_once __DIR__.'/../Controllers/User.php'; // Користувач