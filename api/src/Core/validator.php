<?php

class Validator
{
    /**
     * Перевірка чи пуста змінна
     *
     * @param string $name Назва
     * @param mixed $var Змінна

     */
    public static function isEmpty(string $name, mixed $var)
    {
        $trimmed_var = trim($var ?? "");
        if(empty($trimmed_var)) {
            throw new Exception($name . " is empty!");
        }
    }

    /**
     * Валідація E-mail
     *
     * @param string $email E-mail
     * @throws Exception Помилка у разі якщо E-mail не валідний
     */
    public static function isEmailCorrect(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email вказано не вірно!");
        }
    }

    /**
     * Валідація посилання
     *
     * @param string $url Посилання
     * @throws Exception Помилка у разі якщо посилання не валідне
     */
    public static function isURLCorrect(string $url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception("Посилання вказано не вірно!");
        }
    }
}
