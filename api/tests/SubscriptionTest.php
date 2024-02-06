<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/db.cfg.php';
require_once __DIR__.'/../src/Core/requires.php';

class SubscriptionTest extends TestCase {
    public function testCreate(){

        $user_id = 1;

        $advertisement = new Advertisement("https://www.olx.ua/d/obyavlenie/prodazh-elektrosamokatu-egret-eight-IDUg5YU.html?reason=hp%7Cpromoted");
        
        $result = Subscription::create($user_id, $advertisement);

        $this->assertNull($result);
    }

    public function testGetAllByUser(){
        $user_id = 1;

        $result = Subscription::getAllByUser($user_id);
        $this->assertIsArray($result);
    }
}

