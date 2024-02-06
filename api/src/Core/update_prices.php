<?php

require_once "requires.php";
require_once __DIR__ . "../../../../db.cfg.php";

// NOTE: це просто файл для виклику функції оновлення ціни зовні
// По хорошому його б захистити "паролем", щоб будь хто не міг класти сервіс

OLX::saveNewPrices();