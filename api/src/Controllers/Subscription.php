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

    public static function notificateUsersAboutPriceChange(array $changed_prices)
    {

        $advertisement_ids = array_column($changed_prices, "advertisement_id");

        $matched_users = DB::execRequest("SELECT `users`.`email`, GROUP_CONCAT(`advertisements`.`id`) AS advertisement_ids
                                            FROM `users`
                                            LEFT JOIN `subscriptions` ON `subscriptions`.`user_id` = `users`.`id`
                                            LEFT JOIN `advertisements` ON `advertisements`.`id` = `subscriptions`.`advertisement_id`
                                            WHERE `advertisements`.`id` IN (:ids)
                                            GROUP BY `users`.`id`", ['ids' => $advertisement_ids]);



        $formatted_prices_array = array_reduce($changed_prices, function ($result, $price) {
            $floated_old_price = Price::formatToFloat($price["old_price"]);
            $floated_new_price = Price::formatToFloat($price["new_price"]);

            $calculated_difference = $floated_new_price - $floated_old_price;

            $difference_with_sign = ($calculated_difference >= 0) ? "+$calculated_difference" : "$calculated_difference";

            $result[$price['advertisement_id']] = "<a href='{$price['advertisement_url']}' title='_blank'> {$price['advertisement_title']} - {$price['new_price']} ($difference_with_sign) </a>";
            return $result;
        }, []);

        $users_with_price_changes_messages = array_map(function ($user) use ($formatted_prices_array) {
            $advertisement_ids = explode(',', $user['advertisement_ids']);

            $differences = array_map(function ($ad_id) use ($formatted_prices_array) {
                return $formatted_prices_array[$ad_id] ?? '';
            }, $advertisement_ids);

            $user['message_part'] = implode("<br>", $differences);
            return $user;
        }, $matched_users);


        $mail_subject = MAIL::TEMPLATE_PRICE_CHANGE_SUBJECT;
        foreach ($users_with_price_changes_messages as $current_data) {

            $user_email = $current_data["email"];

            $message_part = $current_data["message_part"];
            $message = MAIL::TEMPLATE_PRICE_CHANGE_MAIL . $message_part;

            Mail::send($user_email, $mail_subject, $message);
        }
    }
}
