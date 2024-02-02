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

        $changed_prices = [];

        foreach ($advertisements as $current_advertisement) {
            $id = $current_advertisement["id"];
            $title = $current_advertisement["title"];

            $slug = $current_advertisement["slug"];
            $url = self::getFullURL($slug);

            $page = self::getURLContent($url);

            $old_price = Price::getLastByAdvertisementID($id);
            
            $new_price = self::getPriceFromPage($page);

            if(empty($new_price)){
                continue;
            }

            if($new_price != $old_price){

                // DB::insert("INSERT INTO `prices` (`advertisement_id`, `price`) VALUES (:advertisement_id, :price)", ["advertisement_id" => $id, "price" => $new_price]);
                
                $changed_prices[] = [
                    "advertisement_id" => $id,
                    "advertisement_title" => $title,
                    "advertisement_url" => $url,
                    "old_price" => $old_price,
                    "new_price" => $new_price
                ];
            }
        }

        Subscription::notificateUsersAboutPriceChange($changed_prices);
    }

    public static function getPriceFromPage($page)
    {
        $xpath = self::getPageXpath($page);
        $anchor = $xpath->query('//h3[@class="css-12vqlj3"]');

        $price = $anchor[0]->nodeValue ?? null;

        return $price;
    }

    public static function getAdvertisementTitle(string $slug){

        $url = self::getFullURL($slug);

        $page = self::getURLContent($url);
        $xpath = self::getPageXpath($page);
        $anchor = $xpath->query('//title');

        $page_title = $anchor[0]->nodeValue;
        $clear_title = explode(":", $page_title)[0];

        return $clear_title;
    }

    private static function getPageXpath($page){
        $doc = new DOMDocument();
        $doc->loadHTML($page);
        $xpath = new DOMXpath($doc);
        return $xpath;
    }

    private static function getFullURL(string $slug){
        return self::URL_PREFIX.$slug;
    }
}
