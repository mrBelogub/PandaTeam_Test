<?php

/**
 * Клас для роботи з оголошеннями
 */
class Advertisement
{
    /**
     * Посилання на оголошення
     */
    private string $url;

    /**
     * Частка посилання, в якій вказана саме сторінка оголошення
     */
    public string $slug;

    /**
     * ID оголошення із бази даних
     */
    public int $id;

    /**
     * Конструктор класу.
     *
     * @param string $url Посилання на оголошення
     */
    public function __construct(string $url)
    {
        $this->url = $url;
        $this->slug = $this->getSlug();
        $this->id = $this->getId();

        // NOTE: З приводу Slug - частки посилання, в якій вказана саме сторінка оголошення.
        // Можна було б назвати це умновно basename, від назви функції в PHP, це не дуже важливо
        // Коротше, це зроблено для того, щоб займати менше місця в бд, щоб вона менше навантажувалась на великих запитах,
        // щоб менше даних передавати туди-сюди в самому PHP тощо.
        // До того ж, якщо посилання зміниться, або OLX змінить структуру посилання - що так що так все не будет працювати,
        // Але в цьому випадку треба буде робити менше змін, аніж якщо зберігати усе посилання
    }

    /**
     * Отримання частки посилання, в якій вказана саме сторінка оголошення
     *
     * @return string частка посилання, в якій вказана саме сторінка оголошення
     * @throws Exception Помилка при обробці посилання
     */
    private function getSlug(): string
    {
        // Отримуємо все що йде після домену
        $path = parse_url($this->url, PHP_URL_PATH);

        // Отримуємо те що йде після останного слешу
        $basename = basename($path);

        // Якщо чомусь не вдалось отримати - видаємо помилку
        if(empty($basename)) {
            throw new Exception("При обробці посилання виникла помилка: не вдалось знайти сторінку");
        }

        // Повертаємо частку посилання, в якій вказана саме сторінка оголошення
        return $basename;
    }

    /**
     * Отримання ID оголошення
     *
     * @return integer ID оголошення
     */
    private function getId(): int
    {
        // Намагаємося найти оголошення в БД
        $data = DB::getOne("SELECT `id` FROM `advertisements` WHERE `slug` = :slug", ["slug" => $this->slug]);
        $id = $data["id"] ?? null;

        // Якщо оголошення немає в бд - записуємо його до бд
        if(empty($id)) {
            $id = $this->create();
        }

        // Повертаємо ID оголошення
        return $id;
    }

    /**
     * Запис оголошення до БД
     *
     * @return integer ID оголошення
     * @throws Exception Помилка при отримуванні даних оголошення
     */
    private function create(): int
    {
        // Отримання даних оголошення з OLX
        $info = OLX::getAdvertisementInfo($this->slug);

        // Якщо по якійсь причині не вдалося отримати дані оголошення з OLX - видаємо помилку
        if(empty($info)) {
            throw new Exception("Помилка отримування даних оголошення");
        }

        // Отримуємо назву оголошення
        $title = $info["title"];
        // NOTE: Можна й не записувати назву до бд а динамічно брати її перед генерацією листа.
        // Але по-перше - тепер можна виводити на фронт більш зрозуміло.
        // А по-друге - це захист від того, що назва зміниться і користувачу на пошту прийде лист де є якийсь товар, який він не додавав.
        // Але якби це було б не тестове - можна було б обсудити як треба.

        // Записуємо оголошення до бд
        $id = DB::insert("INSERT INTO `advertisements` (`title`, `slug`) VALUES (:title ,:slug)", ["title" => $title, "slug" => $this->slug]);

        // Отримуємо ціну оголошення
        $price = $info["price"];

        // Записуємо ціну оголошення
        Price::create($id, $price);
        // NOTE: Можна було б і не записувати ціну при створенні нового запису оголошення в бд, та залишити її пустою до поки не пройде крон
        // Але тоді на фронті перший час (лише для нового оголошення) було б пуста сума і це ввело б користувача в оману, що щось працює не так

        // Повертаємо ID оголошення
        return $id;
    }

    /**
     * Отримати всі оголошення
     *
     * @return array Масив з усіма оголошеннями
     */
    public static function getAll(): array
    {
        // Отримуємо усі оголошення із БД
        $advertisements = DB::execRequest("SELECT * FROM `advertisements`", []);

        // Видаємо усі оголошення, знайдені у БД
        return $advertisements;
    }
}
