<?php

class Price{
    public static function create(){

    }

    public static function getLastByAdvertisementID(int $advertisement_id){
        $data = DB::getOne("SELECT `price` FROM `prices` WHERE `advertisement_id` = :advertisement_id ORDER BY created_at DESC", ["advertisement_id" => $advertisement_id]);
        
        return $data["price"] ?? 0;
    }

    

    public static function getByAdvertisementID(int $advertisement_id){
        $prices_data = DB::execRequest("SELECT `price`, `created_at` FROM `prices` WHERE `advertisement_id` = :id", ["id" => $advertisement_id]);
        return $prices_data ? $prices_data : (object) [];
    }

    public static function formatToFloat($price){
        $cleaned_string = str_replace([" ", "грн."], "", $price);

        $float = floatval($cleaned_string);

        return $float;

    }
}