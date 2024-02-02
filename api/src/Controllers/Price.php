<?php

class Price{
    public static function create(){

    }

    public static function getByAdvertisementID(int $advertisement_id){
        $prices_data = DB::execRequest("SELECT `price`, `created_at` FROM `prices` WHERE `advertisement_id` = :id", ["id" => $advertisement_id]);
        return $prices_data ? $prices_data : (object) [];
    }
}