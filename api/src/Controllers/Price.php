<?php

/**
 * Клас роботи з цінами оголошень
 */
class Price
{
    /**
     * Запис нової ціни оголошення до бд
     *
     * @param integer $advertisement_id ID оголошення
     * @param string $price Ціна оголошення
     * @return void
     */
    public static function create(int $advertisement_id, string $price)
    {
        // Записуємо нову ціну оголошення в БД
        DB::insert("INSERT INTO `prices` (`advertisement_id`, `price`) VALUES (:advertisement_id, :price)", ["advertisement_id" => $advertisement_id, "price" => $price]);
    }

    /**
     * Отримання останної ціни оголошення по його ID
     *
     * @param integer $advertisement_id ID оголошення
     * @return string Ціна оголошення, як вона була вказана на OLX
     */
    public static function getLastByAdvertisementID(int $advertisement_id): string
    {
        $data = DB::getOne("SELECT `price` FROM `prices` WHERE `advertisement_id` = :advertisement_id ORDER BY created_at DESC, `id` DESC;", ["advertisement_id" => $advertisement_id]);

        return $data["price"];
    }

    /**
     * Отримання цін на оголошення для користувача
     *
     * @param integer $advertisement_id ID оголошення
     * @return array|object Масив з цінами оголошення
     */
    public static function getByAdvertisementForUser(int $advertisement_id): array|object
    {
        // Отримуємо ID авторизованого користувача
        $user_id = User::getID();

        // Отримуємо ціни на оголошення для авторизованого користувача
        // які були записані до БД після дати створення підписки на оголошення
        // та однією останною ціною до дати підписки
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
                                            // NOTE:: спочатку це був невеликий SQL запит
                                            // до поки я не вирішив додати ще й передостанню ціну перед датою підписки
                                            // щоб не видавати на фронт пустоту якщо від підписався на створене оголошення
                                            // а ціна на нього ще не оновилась
                                            // P.S. Можна було б видавати взагалі усі ціни цього оголошення, і на робочому проекті
                                            // це треба було б обсудити

        // Повертаємо масив з цінами на оголошення
        return $prices_data;
    }

    /**
     * Перетворення ціни з формату OLX до формату числа з плаваючою точкою
     *
     * @param string $price Ціна, як вона вказана в OLX
     * @return float Значення ціни в форматі числа з плаваючою точкою
     */
    public static function formatToFloat(string $price): float
    {
        // NOTE: це треба лише для вирахування різниці між останною та попередною ціною,
        // щоб лист на e-mail зі змінами цін був біль інформативний

        // Отримуємо те що було вказано в ціні до "грн."
        $cleaned_string = str_replace([" ", "грн."], "", $price);

        // Перетворюємо строкове значення ціни в числове
        $float = floatval($cleaned_string);

        // Повертаємо числове значення ціни
        return $float;
    }
}
