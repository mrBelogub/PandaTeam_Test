<?php

class Advertisement
{
    private string $url;
    public string $slug;
    public int $id;

    public function __construct(string $url)
    {
        $this->url = $url;
        $this->slug = $this->getSlug();
        $this->id = $this->getId();
    }

    private function getSlug()
    {
        $path = parse_url($this->url, PHP_URL_PATH);
        $basename = basename($path);
        if(empty($basename)) {
            throw new Exception("При обробці посилання виникла помилка: не вдалось знайти сторінку");
        }
        return $basename;
    }

    private function getId()
    {
        $data = DB::getOne("SELECT `id` FROM `advertisements` WHERE `slug` = :slug", ["slug" => $this->slug]);
        $id = $data["id"] ?? null;

        if(empty($id)) {
            $id = $this->create();
        }

        return $id;

    }

    private function create()
    {
        $title = OLX::getAdvertisementTitle($this->slug);
        // Можна й не записувати назву до бд а динамічно брати її перед генерацією листа
        // Але по-перше тепер можна виводити на фронт більш зрозуміло
        // А по-друге це захист від того що назва зміниться і користувачу на пошту прийде лист де якийсь товар який він не додавав
        // Але якщо в роботі замовнику (та/або проджект менеджеру) треба буде інакша логіка - будь ласка, змінити не проблема

        $id = DB::insert("INSERT INTO `advertisements` (`title`, `slug`) VALUES (:title ,:slug)", ["title" => $title, "slug" => $this->slug]);
        return $id;
    }

    public static function getAll() {
        $advertisements = DB::execRequest("SELECT * FROM `advertisements`", []);
        return $advertisements;
    }
}
