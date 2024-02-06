<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/db.cfg.php';
require_once __DIR__.'/../src/Core/requires.php';

class OLXTest extends TestCase {
    public function testSaveNewPrices(){

        $user_id = 1;
        $advertisement = new Advertisement("https://www.olx.ua/d/obyavlenie/prodazh-elektrosamokatu-egret-eight-IDUg5YU.html?reason=hp%7Cpromoted");
        
        $result = Subscription::create($user_id, $advertisement);

        DB::execRequest("UPDATE `prices` SET `price` = '1'", []);

        $result = OLX::saveNewPrices();
        $this->assertNull($result);
    }
}

