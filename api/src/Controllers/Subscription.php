<?php

class Subscription
{
    public static function create(int $user_id, Advertisement $advertisement)
    {
        $advertisement_id = $advertisement->id;
        DB::insert("INSERT IGNORE INTO `subscriptions` (`user_id`, `advertisement_id`) VALUES (:user_id, :advertisement_id)", ["user_id" => $user_id, "advertisement_id" => $advertisement_id]);
    }

    public static function getAllByUser(int $user_id)
    {
        // NOTE: наступний SQL скрипт такий великий лише тому, що я вирішив на фрон у список всіх підписок видавати
        // останню ціну, а потім ще й попередню ціну (для відображення різниці та виросла чи впала ціна),
        // щоб користувач міг швидко зорієнтуватись без потреби відкривати кожну підписку.
        // Задля того щоб швидше здати тз (я роблю це у суботу, тобто для уточнень прийшлось би чекати понеділка) я вирішив зробити так
        // Але на роботі, якщо прямо не вказано треба чи не треба це робити - звісно краще було б обсудити с проджектом
        $subscription_data = DB::execRequest("SELECT `subscriptions`.`advertisement_id` as `id`, `advertisements`.`title`, `prices`.`price`, `prices`.`created_at` as `updated_at`,
                                                    (
                                                        SELECT `price`
                                                        FROM `prices`
                                                        WHERE `advertisement_id` = `subscriptions`.`advertisement_id`
                                                        ORDER BY `id` DESC
                                                        LIMIT 1 OFFSET 1
                                                    ) AS `previous_price`
                                                FROM `subscriptions`
                                                LEFT JOIN `prices` ON `prices`.`id` = (
                                                        SELECT MAX(`id`)
                                                        FROM `prices`
                                                        WHERE `advertisement_id` = `subscriptions`.`advertisement_id`
                                                        AND `created_at` = (
                                                            SELECT MAX(`created_at`)
                                                            FROM `prices`
                                                            WHERE `advertisement_id` = `subscriptions`.`advertisement_id`
                                                        )
                                                    )
                                                LEFT JOIN `advertisements` ON `advertisements`.`id` = `subscriptions`.`advertisement_id`
                                                WHERE `subscriptions`.`user_id` = :user_id
                                                ORDER BY `subscriptions`.`id` DESC;", ["user_id" => $user_id]);
        // NOTE: Можна було б зробити інші варіанти сортування
        // Наприклад: останні додані спочатку, або остання змінена ціна спочатку
        // Або додати варіацію сортування в залежності від вибору на фронті
        // А оскільки вимог не було - я вирішив зробити так, щоб нові підписки були зверху
        // Але якби це було б не тестове - можна було б обсудити як треба зробити
        return $subscription_data;
    }


    public static function notificateUsersAboutPriceChange(array $changed_prices)
    {
        $advertisement_ids = array_column($changed_prices, "advertisement_id");

        $formatted_prices_array = self::getFormattedPriceArray($changed_prices);

        $matched_users = DB::execRequest("SELECT `users`.`email`, GROUP_CONCAT(`advertisements`.`id`) AS advertisement_ids
                                            FROM `users`
                                            LEFT JOIN `subscriptions` ON `subscriptions`.`user_id` = `users`.`id`
                                            LEFT JOIN `advertisements` ON `advertisements`.`id` = `subscriptions`.`advertisement_id`
                                            WHERE `advertisements`.`id` IN (:ids)
                                            GROUP BY `users`.`id`", ['ids' => $advertisement_ids]);

        $users_with_price_changes_messages = self::getUsersWithPriceChangesMessages($matched_users, $formatted_prices_array);

        $mail_subject = MAIL::TEMPLATE_PRICE_CHANGE_SUBJECT;
        foreach ($users_with_price_changes_messages as $current_data) {

            $user_email = $current_data["email"];

            $message_part = $current_data["message_part"];
            $message = MAIL::TEMPLATE_PRICE_CHANGE_MAIL . $message_part;

            Mail::send($user_email, $mail_subject, $message);
        }
    }

    private static function getFormattedPriceArray(array $changed_prices): array
    {
        $formatted_prices_array = array_reduce($changed_prices, function ($result, $price) {
            $floated_old_price = Price::formatToFloat($price["old_price"]);
            $floated_new_price = Price::formatToFloat($price["new_price"]);

            $calculated_difference = $floated_new_price - $floated_old_price;

            $difference_with_sign = ($calculated_difference >= 0) ? "+$calculated_difference" : "$calculated_difference";

            $result[$price['advertisement_id']] = "<a href='{$price['advertisement_url']}' title='_blank'> {$price['advertisement_title']}</a> - {$price['new_price']} ($difference_with_sign)<br>";
            return $result;
        }, []);

        return $formatted_prices_array;
    }

    private static function getUsersWithPriceChangesMessages(array $matched_users, array $formatted_prices_array): array
    {
        $users_with_price_changes_messages = array_map(function ($user) use ($formatted_prices_array) {
            $advertisement_ids = explode(',', $user['advertisement_ids']);

            $differences = array_map(function ($ad_id) use ($formatted_prices_array) {
                return $formatted_prices_array[$ad_id] ?? '';
            }, $advertisement_ids);

            $user['message_part'] = implode("<br>", $differences);
            return $user;
        }, $matched_users);

        return $users_with_price_changes_messages;
    }
}
