<?php

// Ігнор ворнінгів, бо їх з ОЛХ дуже багато через пробеми з їх версткою
error_reporting(error_reporting() & ~E_WARNING);

/**
 * Клас для роботи з OLX
 */
class OLX
{
    // Строка яка йде у кожному посиланні на оголошення перед самим шляхом до html
    private const URL_PREFIX = "https://www.olx.ua/d/uk/obyavlenie/";


    /**
     * Оновити ціни на оголошення
     *
     * @return void
     */
    public static function saveNewPrices()
    {
        // Встановлюємо максимальний час виконання скрипту
        ini_set('max_execution_time', 600);

        // Отримуємо усі оголошення
        $advertisements = Advertisement::getAll();

        // Створюємо пустий масив для зберігання оголошень у яких змінилась ціна
        $changed_prices = [];

        // Перевіряємо кожне оголошення
        foreach ($advertisements as $current_advertisement) {

            // Отримуємо інформацію про нього з бд
            $id = $current_advertisement["id"];
            $title = $current_advertisement["title"];
            $slug = $current_advertisement["slug"];

            // Отримуємо останню збережену в БД ціну
            $old_price = Price::getLastByAdvertisementID($id);

            // Отримуємо поточну ціну із оголошення
            $new_price = self::getAdvertisementInfo($slug)["price"] ?? null;

            // Якщо чомусь не вийшло оновити ціну - пропускаємо цю ітерацію
            if(empty($new_price)) {
                continue;
            }

            // Перевіряємо, чи співпадає нова ціна зі старою
            if($new_price != $old_price) {
                // Якщо нова ціна не співпадає зі старою - записуємо нову ціну до бд
                Price::create($id, $new_price);

                // Отримуємо повне посилання на оголошення
                $url = self::getFullURL($slug);

                // Зберігаємо у масив інформацію про зміну ціни у цьому оголошенні
                $changed_prices[] = [
                    "advertisement_id" => $id,
                    "advertisement_title" => $title,
                    "advertisement_url" => $url,
                    "old_price" => $old_price,
                    "new_price" => $new_price
                ];
            }
        }

        // Якщо в жодному оголошенні не змінилась ціна - завершуємо роботу функції
        if (empty($changed_prices)) {
            return;
        }

        // Сповіщаємо усіх користувачів, у яких в підписках змінилась ціна
        Subscription::notificateUsersAboutPriceChange($changed_prices);
    }

    /**
     * Отримання інформації про оголошення
     *
     * @param string $slug Частка посилання, в якій вказана саме сторінка оголошення
     * @return array Інформація про оголошення
     */
    public static function getAdvertisementInfo(string $slug): array
    {
        // Отримуємо Xpath сторінки оголошення
        $xpath = self::getPageXpath($slug);

        // Отримуємо назву на ціну оголошення
        $title = self::getAdvertisementTitle($xpath);
        $price = self::getAdvertisementPrice($xpath);

        // Якшо не вдалось отримати назву або ціну оголошення - повертаємо пустий масив
        if(empty($title) || empty($price)) {
            // NOTE: тут можна було б викидувати ексепшн, але є місце в коді де не можна викидувати ексепшн в цьому випадку
            return [];
        }

        // Формуємо масив з даними про оголошення
        $result = [
            "title" => $title,
            "price" => $price
        ];

        // Повертаємо масив з даними про оголошення
        return $result;
    }

    /**
     * Отримання назви оголошення
     *
     * @param DOMXpath $xpath Xpath сторінки оголошення
     * @return string Назва оголошення
     */
    private static function getAdvertisementTitle(DOMXpath $xpath): string
    {
        // Шукаємо елемент
        $anchor = $xpath->query('//title');

        // Отримуємо його значення
        $page_title = $anchor[0]->nodeValue ?? "";

        // Якщо назву отримати не вдалось - повертаємо пусту строку
        if(empty($page_title)){
            return $page_title;
        }

        // Отримуємо чисту назву оголошення (вона вказана в title до двокрапки)
        $clear_title = trim(explode(":", $page_title)[0]);

        // Повертаємо назву оголошення
        return $clear_title;
    }

    /**
     * Отримання ціни
     *
     * @param DOMXpath $xpath Xpath сторінки оголошення
     * @return string Ціна, так як вона вказана в оголошенні
     */
    private static function getAdvertisementPrice(DOMXpath $xpath): string
    {
        // NOTE: поясню: чому повертаємо ціну "так як вона вказана в оголошенні":
        // По-перше вона може бути з "грн.", "$", або "€" в кінці
        // По друге - вона може бути вказано "/ за 1 штуку" або ще якось
        // Тому для видачі на фронт, а також для листа, я вирішив зберігати в БД ціну саме так, як вона написана в OLX

        // Шукаємо елемент
        $anchor = $xpath->query('//h3[@class="css-12vqlj3"]');

        // Отримуємо його значення
        $price = $anchor[0]->nodeValue ?? "";

        // Повертаємо ціну оголошення
        return $price;
    }

    /**
     * Отримання Xpath сторінки оголошення
     *
     * @param string $slug Частка посилання, в якій вказана саме сторінка оголошення
     * @return DOMXpath Xpath сторінки оголошення
     */
    private static function getPageXpath(string $slug): DOMXpath
    {
        // Отримуємо повне посилання на оголошення
        $url = self::getFullURL($slug);
        // Отримуємо сторінку оголошення
        $page = self::getContents($url);

        // Якщо не вдалось отримати сторінку оголошення - видаємо помилку
        if(!$page) {
            throw new Exception("Помилка при обробці оголошення");
        }

        // Перетворюємо отриманий код сторінки в Xpath елемент
        $doc = new DOMDocument();
        $doc->loadHTML($page);
        $xpath = new DOMXpath($doc);

        // Повертаємо Xpath сторінки
        return $xpath;
    }


    /**
     * Отримання повного посилання на оголошення
     *
     * @param string $slug Частка посилання, в якій вказана саме сторінка оголошення
     * @return string Повне посилання на оголошення
     */
    public static function getFullURL(string $slug): string
    {
        // Поєднуємо статичний початок посилання з самою html сторінкою оголошення та повертаємо його
        return self::URL_PREFIX . $slug;
    }

    /**
     * Отримання сторінки оголошення
     *
     * @param string $url Посилання на оголошення
     * @return string Код сторінки оголошення
     */
    private static function getContents(string $url): string
    {
        // NOTE:: Аналог функції file_get_contents який на тестах вибрикувався,
        // тож я вирішив його замінити на більш стабільний curl

        // Отримуємо код сторінки оголошення
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        // Повертаємо код сторінки оголошення
        return $result;
    }
}
