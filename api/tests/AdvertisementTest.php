<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/db.cfg.php';
require_once __DIR__.'/../src/Core/requires.php';

class AdvertisementTest extends TestCase {
    public function testAdvertisementCreationWithValidUrl() {

        DB::execRequest("DELETE FROM `advertisements`", []);
        DB::execRequest("ALTER TABLE `advertisements` AUTO_INCREMENT = 1", []);

        $url = 'https://www.olx.ua/d/obyavlenie/prodazh-elektrosamokatu-egret-eight-IDUg5YU.html?reason=hp%7Cpromoted';
        $advertisement = new Advertisement($url);

        $this->assertInstanceOf(Advertisement::class, $advertisement);
        $this->assertIsString($advertisement->slug);
        $this->assertIsInt($advertisement->id);
    }

    public function testAdvertisementCreationWithInvalidUrl() {
        $this->expectException(Exception::class);
        $url = 'https://www.olx.ua/d/obyavlenie/prodazh-elektrosamokatu';
        new Advertisement($url);
    }

    public function testAdvertisementCreationWithNoURL() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("При обробці посилання виникла помилка: не вдалось знайти сторінку");
        $url = '';
        new Advertisement($url);
    }

    public function testGetAllAdvertisements() {

        DB::execRequest("DELETE FROM `advertisements`", []);
        DB::execRequest("ALTER TABLE `advertisements` AUTO_INCREMENT = 1", []);

        // Creating some advertisements for testing
        new Advertisement('https://www.olx.ua/d/obyavlenie/prodazh-elektrosamokatu-egret-eight-IDUg5YU.html?reason=hp%7Cpromoted');
        new Advertisement('https://www.olx.ua/d/obyavlenie/lincoln-mkx-reserve-IDRG0LM.html');

        // Retrieving all advertisements
        $advertisements = Advertisement::getAll();

        $this->assertIsArray($advertisements);
        $this->assertCount(2, $advertisements);
    }

}
