<?php

error_reporting(error_reporting() & ~E_WARNING); // Бо з ОЛХ приходить багато ворнінгів через їх верстку

class OLX
{
    public const URL_PREFIX = "https://www.olx.ua/d/uk/obyavlenie/";

    public static function getURLContent(string $url)
    {
        $content = file_get_contents($url);
        return $content;
    }


    public static function saveNewPrices()
    {
        $advertisements = Advertisement::getAll();

        foreach ($advertisements as $current_advertisement) {
            $id = $current_advertisement["id"];

            $slug = $current_advertisement["slug"];
            $url = self::URL_PREFIX . $slug;

            $page = self::getURLContent($url);

            $price = self::getPriceFromPage($page);

            DB::insert("INSERT INTO `prices` (`advertisement_id`, `price`) VALUES (:advertisement_id, :price)", ["advertisement_id" => $id, "price" => $price]);
        }


    }

    public static function getPriceFromPage($page)
    {
        $doc = new DOMDocument();
        $doc->loadHTML($page);
        $xpath = new DOMXpath($doc);

        $course = 0;
        $anchor = $xpath->query('//h3[@class="css-12vqlj3"]');

        $price = $anchor[0]->nodeValue;

        return $price;
    }
}
