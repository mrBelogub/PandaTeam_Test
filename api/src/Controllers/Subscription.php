<?php

/**
 * Клас для роботи з підписками
 */
class Subscription
{
    /**
     * Створення новоЇ підписки
     *
     * @param integer $user_id ID користувача
     * @param Advertisement $advertisement оголошення
     * @return void
     */
    public static function create(int $user_id, Advertisement $advertisement)
    {
        // Отримуємо ID оголошення
        $advertisement_id = $advertisement->id;

        // Записуємо підписку до БД
        DB::insert("INSERT IGNORE INTO `subscriptions` (`user_id`, `advertisement_id`) VALUES (:user_id, :advertisement_id)", ["user_id" => $user_id, "advertisement_id" => $advertisement_id]);
    }

    public static function getAllByUser(int $user_id)
    {
        // NOTE: наступний SQL скрипт такий великий лише тому, що я вирішив на фрон у список всіх підписок видавати
        // останню ціну, а потім ще й попередню ціну (для відображення різниці та виросла чи впала ціна),
        // щоб користувач міг швидко зорієнтуватись без потреби відкривати кожну підписку.
        // Задля того щоб швидше здати тз (я роблю це у суботу, тобто для уточнень прийшлось би чекати понеділка) я вирішив зробити так
        // Але на роботі, якщо прямо не вказано треба чи не треба це робити - звісно краще було б обсудити с проджектом
        $subscription_data = DB::execRequest("SELECT `subscriptions`.`advertisement_id` as `id`, `advertisements`.`title`, `prices`.`price`, `prices`.`created_at` as `updated_at`, CONCAT(:url_prefix, `advertisements`.`slug`) as `url`,
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
                                                ORDER BY `subscriptions`.`id` DESC;", ["user_id" => $user_id, "url_prefix" => OLX::URL_PREFIX]);
        // NOTE: Можна було б зробити інші варіанти сортування
        // Наприклад: останні додані спочатку, або остання змінена ціна спочатку
        // Або додати варіацію сортування в залежності від вибору на фронті
        // А оскільки вимог не було - я вирішив зробити так, щоб нові підписки були зверху
        // Але якби це було б не тестове - можна було б обсудити як треба зробити
        return $subscription_data;
    }

    /**
     * Сповіщення користувачів про зміну в ціні
     *
     * @param array $changed_prices Масив з інформацією про зміни в цінах
     * @return void
     */
    public static function notificateUsersAboutPriceChange(array $changed_prices)
    {
        // Отримуємо ID оголошень, в яких змінились ціни
        $advertisement_ids = array_column($changed_prices, "advertisement_id");

        // Генеруємо до кожної зміни ціни відповідну строку для листа та додаємо до масиву
        $formatted_prices_array = self::getFormattedPriceArray($changed_prices);

        // Отримуємо користувачів, в підписках у яких змінились ціни на оголошення
        $matched_users = DB::execRequest("SELECT `users`.`email`, GROUP_CONCAT(`advertisements`.`id`) AS `advertisement_ids`
                                            FROM `users`
                                            LEFT JOIN `subscriptions` ON `subscriptions`.`user_id` = `users`.`id`
                                            LEFT JOIN `advertisements` ON `advertisements`.`id` = `subscriptions`.`advertisement_id`
                                            WHERE `advertisements`.`id` IN (:ids)
                                            GROUP BY `users`.`id`", ['ids' => $advertisement_ids]);

        // Додаємо до масиву користувачів інформацію про зміну цін відповідно до їх підписок
        $users_with_price_changes_messages = self::getUsersWithPriceChangesMessages($matched_users, $formatted_prices_array);

        // Отримуємо тему листа
        $mail_subject = MAIL::TEMPLATE_PRICE_CHANGE_SUBJECT;

        // Проходимось по кожному елементу масива з користувачами та їх повідомленнями
        foreach ($users_with_price_changes_messages as $current_data) {
            // Отримуємо E-mail адресу конкретного користуача
            $user_email = $current_data["email"];

            // Отримуємо повідомлення для цього користувача
            $message_part = $current_data["message_part"];

            // Формуємо повідомлення на основі шаблону
            $message = MAIL::TEMPLATE_PRICE_CHANGE_MAIL . $message_part;
            // NOTE: тут міг би бути будь який шаблон, хоч HTML сторінка,
            // в яку потім замість шаблонних змінних можна було підставляти дані

            // Відправляємо листа користувачу
            Mail::send($user_email, $mail_subject, $message);
        }
    }

    /**
     * Додавання до кожної зміни ціни відповідної строки для листів
     *
     * @param array $changed_prices Масив з інформацією про зміну цін
     * @return array Масив з доданими строками для листів
     */
    private static function getFormattedPriceArray(array $changed_prices): array
    {
        // Проходимо кожен елемент масиву, генеруємо та додаємо відповідну строку, яку буде використано у листі
        $formatted_prices_array = array_reduce($changed_prices, function ($result, $price) {

            // Отримуємо числові значення попередної та поточної цін
            $floated_old_price = Price::formatToFloat($price["old_price"]);
            $floated_new_price = Price::formatToFloat($price["new_price"]);

            // Рахуємо різницю
            $calculated_difference = $floated_new_price - $floated_old_price;

            // Якщо різниця більше нуля - додаємо до неї символ плюса, по аналогїї з тим як це зроблено у відʼємних числах
            $difference_with_sign = ($calculated_difference >= 0) ? "+$calculated_difference" : "$calculated_difference";

            // Додаємо до нового масиву відображення інформації в форматі для листа по ID оголошення
            $result[$price['advertisement_id']] = "<a href='" . $price['advertisement_url'] . "' title='_blank'> " . $price['advertisement_title'] . "</a> - " . $price['new_price'] . " (" . $difference_with_sign . ")<br>";

            // Повертаємо результат
            return $result;
        }, []);

        // Повертаємо згенерований масив
        return $formatted_prices_array;
    }

    /**
     * Додавання до кожного користувача інформації про зміну цін відповідно до його підписок
     *
     * @param array $matched_users Массив користувачів з їх підписками
     * @param array $formatted_prices_array Масив оголошнь з відображенням інформації для листа
     * @return array Масив з інформацією про зміну цін по оголошенням відповідно для кожного користувача
     */
    private static function getUsersWithPriceChangesMessages(array $matched_users, array $formatted_prices_array): array
    {
        // Проходимо кожен елемент масиву та додаємо до нього інформацію про зміну цін відповідно до підписок на оголошення користувача
        $users_with_price_changes_messages = array_map(function ($user) use ($formatted_prices_array) {
            // Отримуємо ID оголошень на які користувач підписаний
            $advertisement_ids = explode(',', $user['advertisement_ids']);

            // Знаходимо інформацію про різницю у цінах по оголошенням на які підписаний цей користувач
            $differences = array_map(function ($ad_id) use ($formatted_prices_array) {
                return $formatted_prices_array[$ad_id] ?? '';
            }, $advertisement_ids);

            // Додаємо повідомлення до елементу масиву цього користувача
            $user['message_part'] = implode("<br>", $differences);

            // Повертаємо оброблений елемент
            return $user;
        }, $matched_users);

        // Повертаємо масив в якому до кожного користувача додані інформації про зміну цін відповідно до оголошень на які він підписаний
        return $users_with_price_changes_messages;
    }
}
