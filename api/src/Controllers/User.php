<?php

/**
 * Клас для роботи з користучаем
 */
class User
{
    /**
     * Отримання ID авторизованого користувача
     *
     * @return integer ID авторизованого користувача
     */
    public static function getID(): int
    {
        // Отримуємо ID із сессії
        $user_id = $_SESSION['user_id'];

        // Якщо не вдалось отримати ID - видаємо помилку
        if (empty($user_id)) {
            throw new Exception("Ви не авторизовані!");
        }

        // Повертаємо ID користувача
        return $user_id;
    }

    /**
     * Отримання інформації із БД про авторизованого користувача
     *
     * @return array інформація із БД про авторизованого користувача
     */
    public static function getData(): array
    {
        // Отримуємо ID авторизованого користувача
        $id = self::getID();

        // Отримуємо інформацію про авторизованого користувача із БД за його ID
        $data = DB::getOne("SELECT * FROM `users` WHERE `id` = :id", ["id" => $id]);

        // Повертаємо інформацію про користувача
        return $data;
    }

    /**
     * Отримання інформації про користувача за його E-mail
     *
     * @param string $email E-mail користувача
     * @return array|false інформація про користувача із БД (або false, якщо не знайдено)
     */
    public static function getDataByEmail(string $email): array|false
    {
        // Отримуємо інформацію про користувача із БД за його E-mail
        $data = DB::getOne("SELECT * FROM `users` WHERE `email` = :email", ["email" => $email]);

        // Повертаємо інформацію про користувача
        return $data;
    }

    /**
     * Реєстрація
     *
     * @param string $email E-mail
     * @param string $password Пароль
     * @return integer
     */
    public static function signUp(string $email, string $password): int
    {
        // Отримуємо шифрований хеш паролю
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Записуємо інформацію про користувача до БД
        $user_id = DB::insert("INSERT INTO `users` (`email`, `password_hash`) VALUES (:email, :password_hash)", ["email" => $email, "password_hash" => $password_hash]);

        // Якщо не вдалось записати інформацию про користувача до БД - видаємо помилку
        if(empty($user_id)) {
            throw new Exception("При реєстрації виникла помилка");
        }

        // Повертаємо ID користувача
        return $user_id;
    }

    /**
     * Відправка коду активації користувача
     *
     * @param string $email E-mail
     * @param boolean $should_throw_exception чи треба видавати помилку
     * @return void
     */
    public static function sendActivationCode(string $email, bool $should_throw_exception = false)
    {
        // Генеруємо унікальний код активації користувача
        $code = self::generateActivationCode($email);

        // Генеруємо посилання на активацію користувача
        $server_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
        $activation_url = $server_url . $_SERVER['HTTP_HOST'] . "/api/activateProfile?email=" . $email . "&code=" . $code;

        // Підставляємо поисалння на активацію до шаблону листа
        $message = str_replace("%URL%", $activation_url, MAIL::TEMPLATE_SIGNUP_ACTIVATION_MESSAGE);

        // Відправляємо лист
        $mail_sended = Mail::send($email, MAIL::TEMPLATE_SIGNUP_ACTIVATION_SUBJECT, $message);

        // Обробка якщо лист не відправлено
        if(!$mail_sended) {

            // Якщо вказано, що треба видати помилку - видаємо помилку
            if ($should_throw_exception) {
                throw new Exception("При надсиланні листа виникла помилка!");
                // NOTE: Це треба скоріш для фронту для запиту про повторне відсилання коду.
                // Але якщо це було при реєстрації - то в даному кейсі відправка листа не має
                // заважати реєстрації користувача.
            }

            // Виходимо з функціїї
            return;
        }

        // Запис нового коду до БД
        DB::execRequest("UPDATE `users` SET `activation_code` = :code WHERE `email` = :email;", ["code" => $code, "email" => $email]);
        // NOTE: Робимо спробу записати до БД після успішного надсилання листа для того, щоб попередній код активації був дійсним якщо ми не змогли надіслати нового листа
        // Але у цьому є свої мінуси - якщо не вийде записати новий код до БД - користувач не зможе активувати профіль
    }

    /**
     * Генерація нового коду активації
     *
     * @param string $string будь яка строка, за замовчуванням - пуста
     * @return string Новий код активаціїї
     */
    private static function generateActivationCode(string $string = ""): string
    {
        // NOTE: ехнічно - тут може бути хоть що, будь яка логіка генерації,
        // просто я абстрагував це від коду основного методу

        // Генеруємо та повертаємо код
        return md5(time() . $string . rand(0, 100000));
    }

    /**
     * Перевірка, чи є користувач по такому посиланню активації
     *
     * @param string $email E-mail
     * @param string $code Код активації
     * @throws Exception Помилка, якщо за цим E-mail та кодом немає співпадань у БД
     * @return void
     */
    public static function isNotActivated(string $email, string $code)
    {
        // Отримуємо інформацію по користувачу з вказаним E-mail та кодом активації
        $user_data = DB::getOne("SELECT `id` FROM `users` WHERE `email` = :email AND `activation_code` = :code;", ["email" => $email, "code" => $code]);

        // Якщо користувача не знайдено - видаємо помилку
        if(empty($user_data)) {
            throw new Exception("Це посилання для активації недійсне.");
        }
    }

    /**
     * Активація профіля користувача
     *
     * @param string $email E-mail
     * @param string $code Код активації
     * @return void
     */
    public static function activate(string $email, string $code)
    {
        // Видаляємо код активації із БД для цього користувача
        DB::execRequest("UPDATE `users` SET `activation_code` = NULL WHERE `email` = :email  AND `activation_code` = :code;", ["email" => $email, "code" => $code]);
    }


    /**
     * Авторизація
     *
     * @param string $email E-mail
     * @param string $password Пароль
     * @throws Exception Помилка якщо користувач увів невірний E-mail або пароль не піходить
     * @return void
     */
    public static function signIn(string $email, string $password)
    {
        // Отримуємо користувача за вказаним E-mail
        $user_data = DB::getOne("SELECT * FROM `users` WHERE `email` = :email", [$email]);

        // Якщо користувача не знайдено АБО пароль не піходить - видаємо помилку
        if (empty($user_data) || !password_verify($password, $user_data['password_hash'])) {
            throw new Exception("Ви ввели неправильний email або пароль");
        }

        // Якщо ж все добре - записуємо в сессію інформацію
        $_SESSION['user_id'] = $user_data["id"];
    }


    /**
     * Створення користувача якщо він вирішив просто підписатись на оголошення
     *
     * @param string $email E-mail
     * @return integer ID користувача
     */
    public static function createFromSubscriptionForm(string $email): int
    {
        // Спочатку намагаємося знайти в БД користувача з таким E-mail
        $user_data = self::getDataByEmail($email);
        $user_id = $user_data["id"] ?? null;

        // Якщо користувача з таким E-mail не знайдено - намагаємося створити
        if(empty($user_id)) {
            $user_id = self::signUp($email, $email);

            // Якщо не вийшло створити користувача - видаємо помилку
            if(empty($user_id)) {
                throw new Exception("При обробці E-mail виникла помилка");
            }
        }

        // Повертаємо ID користувача
        return $user_id;
    }


    /**
     * Вихід із аккаунту
     *
     * @return void
     */
    public static function signOut()
    {
        // Очищуємо та завершуємо сессію
        $_SESSION['user_id'] = "";
        unset($_SESSION);

        session_unset();
        session_destroy();
    }
}
