<?php

class User
{
    public static function getID()
    {
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
            throw new Exception("Ви не авторизовані!");
        }
        return $user_id;
    }

    public static function getData()
    {
        $id = self::getID();
        $data = DB::getOne("SELECT * FROM `users` WHERE `id` = :id", ["id" => $id]);
        return $data;
    }

    private static function getDataByEmail($email)
    {
        $data = DB::getOne("SELECT * FROM `users` WHERE `email` = :email", ["email" => $email]);
        return $data;
    }

    public static function signUp(string $email, string $password)
    {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Реєструємо
        $user_id = DB::insert("INSERT IGNORE INTO `users` (`email`, `password_hash`) VALUES (:email, :password_hash)", ["email" => $email, "password_hash" => $password_hash]);

        if(empty($user_id)) {
            return;
            // Це зроблено для того щоб унеможливити перебором знайти який емейл зареєстровано а який ні
            // Можна було б робити спочатку SELECT до бд (плюс - не інкрементувало б ID, мінус - +1 запит до бд, то я вирішив що не треба зайвий запит до БД)
        }

        return $user_id;
    }

    public static function sendActivationCode(string $email, bool $should_throw_exception = false)
    {
        $code = md5(time() . $email . rand(0, 100000));

        $server_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
        $activation_url = $server_url . $_SERVER['HTTP_HOST'] . "/api/activateProfile?email=".$email."&code=" . $code;

        $message = str_replace("%URL%", $activation_url, MAIL::TEMPLATE_SIGNUP_ACTIVATION_MESSAGE);

        $mail_sended = Mail::send($email, MAIL::TEMPLATE_SIGNUP_ACTIVATION_SUBJECT, $message);

        if(!$mail_sended) {

            if ($should_throw_exception) {
                throw new Exception("При надсиланні листа виникла помилка!");
            }

            return;
        }

        DB::execRequest("UPDATE `users` SET `activation_code` = :code WHERE `email` = :email;", ["code" => $code, "email" => $email]);
    }

    public static function isNotActivated(string $email, string $code)
    {
        $user_data = DB::getOne("SELECT `id` FROM `users` WHERE `email` = :email AND `activation_code` = :code;", ["email" => $email, "code" => $code]);

        if(empty($user_data)) {
            throw new Exception("Це посилання для активації недійсне.");
        }
    }

    public static function activate(string $email, string $code)
    {
        DB::execRequest("UPDATE `users` SET `activation_code` = NULL WHERE `email` = :email  AND `activation_code` = :code;", ["email" => $email, "code" => $code]);
    }


    public static function signIn(string $email, string $password)
    {
        $user_data = DB::getOne("SELECT * FROM `users` WHERE `email` = :email", [$email]);

        if (empty($user_data) || !password_verify($password, $user_data['password_hash'])) {
            throw new Exception("Ви ввели неправильний email або пароль");
        }

        // Якщо ж все добре - записуємо в сессію інформацію
        $_SESSION['user_id'] = $user_data["id"];
    }

    public static function createFromSubscriptionForm($email)
    {
        $user_data = self::getDataByEmail($email);
        $user_id = $user_data["id"] ?? null;

        if(empty($user_id)) {
            $user_id = self::signUp($email, $email);

            if(empty($user_id)) {
                throw new Exception("При обробці E-mail виникла помилка");
            }
        }

        return $user_id;
    }

    public static function signOut()
    {
        // Очищуємо та завершуємо сессію
        $_SESSION['user_id'] = "";
        unset($_SESSION);

        session_unset();
        session_destroy();
    }
}
