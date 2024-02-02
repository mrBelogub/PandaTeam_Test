<?php

class Subscription
{
    public static function create(int $user_id, Advertisement $advertisement)
    {
        $advertisement_id = $advertisement->id;
        DB::insert("INSERT IGNORE INTO `subscriptions` (`user_id`, `advertisement_id`) VALUES (:user_id, :advertisement_id)", ["user_id" => $user_id, "advertisement_id" => $advertisement_id]);
    }

    public static function getByUser(int $user_id)
    {
        $subscription_data = DB::execRequest("SELECT `subscriptions`.`advertisement_id`, `prices`.`price`, `prices`.`created_at`
                                                FROM `subscriptions`
                                                LEFT JOIN `prices` ON `prices`.`advertisement_id` = `subscriptions`.`advertisement_id`
                                                    AND `prices`.`created_at` = (
                                                        SELECT MAX(`created_at`)
                                                        FROM `prices`
                                                        WHERE `advertisement_id` = `subscriptions`.`advertisement_id`
                                                    )
                                                WHERE `subscriptions`.`user_id` = :user_id;", ["user_id" => $user_id]);
        return $subscription_data;
    }
}
