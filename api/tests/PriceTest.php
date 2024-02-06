<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/db.cfg.php';
require_once __DIR__.'/../src/Core/requires.php';

class PriceTest extends TestCase {
    public function testGetByAdvertisementForUser(){

        $_SESSION["user_id"] = 1;

        $advertisement_id = 1;

        $result = Price::getByAdvertisementForUser($advertisement_id);
        $this->assertIsArray($result);
    }
}

