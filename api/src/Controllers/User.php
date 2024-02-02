<?php

class User
{
    public static function getID(){
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)){
            // 
            header("Location: ../login.php");
            exit();
        }
        return $user_id;
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

        self::sendConfirmationCode($email);
    }

    public static function sendConfirmationCode(string $email, bool $should_throw_exception = false)
    {
        $code = rand(0, 10000);

        $server_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" . "://" . $_SERVER['HTTP_HOST'];
        $confirmation_url = $server_url . "/activateProfile?email=".$email."&code=" . $code;

        $message = str_replace("%URL%", $confirmation_url, MAIL::TEMPLATE_SIGNUP_CONFIRMATION_MESSAGE);

        $mail_sended = Mail::send($email, MAIL::TEMPLATE_SIGNUP_CONFIRMATION_SUBJECT, $message);

        if (!$mail_sended && $should_throw_exception) {
            throw new Exception("При надсиланні листа виникла помилка!");
        }

        DB::execRequest("UPDATE `users` SET `confirmation_code` = :code WHERE `email` = :email;", ["code" => $code, "email" => $email]);
    }

    public static function isUnconfirmed(string $email, string $code)
    {
        $user_data = DB::getOne("SELECT `id` FROM `user` WHERE `email` = :email AND `confirmation_code` = :code;", ["email" => $email, "code" => $code]);
        if(empty($user_data)) {
            throw new Exception("Акаунту за вказаним кодом не знайдено!");
        }
    }

    public static function confirm(string $email, string $code)
    {
        DB::execRequest("UPDATE `users` SET `confirmation_code` = NULL WHERE `email` = :email AND `confirmation_code` = :code;", ["email" => $email, "code" => $code]);
    }


    public static function signIn(string $email, string $password)
    {
        $user_data = DB::getOne("SELECT * FROM `users` WHERE `email` = :email", [$email]);

        if (empty($user_data) || !password_verify($password, $user_data['password_hash'])) {
            throw new Exception("Ви ввели неправильний email або пароль");
        }

        // Якщо ж все добре - записуємо в сессію інформацію
        $_SESSION['user_id'] = $user_data["id"];

        // Та перекидуємо на головну сторінку
        header("Location: ../index.php");
        exit();

    }

    public static function signOut()
    {
        // Очищуємо та завершуємо сессію
        $_SESSION['user_id'] = "";
        unset($_SESSION);

        session_unset();
        session_destroy();

        // Перекидуємо на сторінку авторизациії
        header("Location: ../login.php");
        exit();
    }




}
