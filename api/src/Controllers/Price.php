<?php

class Price{
    public static function create(int $advertisement_id, string $price){
        DB::insert("INSERT INTO `prices` (`advertisement_id`, `price`) VALUES (:advertisement_id, :price)", ["advertisement_id" => $advertisement_id, "price" => $price]);
    }

    public static function getLastByAdvertisementID(int $advertisement_id){
        $data = DB::getOne("SELECT `price` FROM `prices` WHERE `advertisement_id` = :advertisement_id ORDER BY created_at DESC, `id` DESC;", ["advertisement_id" => $advertisement_id]);
        
        return $data["price"] ?? 0;
    }

    public static function getByAdvertisementForUser(int $advertisement_id){
        $user_id = User::getID();

        $prices_data = DB::execRequest("SELECT `price`, `prices`.`created_at`
                                            FROM `prices`
                                            LEFT JOIN `subscriptions` ON `subscriptions`.`user_id` = :user_id AND `subscriptions`.`advertisement_id` = `prices`.`advertisement_id`
                                            WHERE `prices`.`advertisement_id` = :advertisement_id 
                                            AND (`prices`.`created_at` >= `subscriptions`.`created_at` OR (
                                                    `prices`.`created_at` = (
                                                        SELECT MAX(`created_at`)
                                                        FROM `prices`
                                                        WHERE `advertisement_id` = `subscriptions`.`advertisement_id`
                                                        AND `created_at` < `subscriptions`.`created_at`
                                                    )
                                                )
                                            );", ["user_id" => $user_id, "advertisement_id" => $advertisement_id]);
                                            // NOTE:: Цей SQL запит був значно меншим до поки я не вирішив додати ще й останню ціну перед датою підписки
        return $prices_data ? $prices_data : (object) [];
    }

    public static function formatToFloat($price){
        $cleaned_string = str_replace([" ", "грн."], "", $price);

        $float = floatval($cleaned_string);

        return $float;

    }
}