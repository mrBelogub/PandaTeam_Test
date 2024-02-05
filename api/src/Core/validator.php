<?php

class Validator
{
    /**
     * Перевірка чи пуста змінна
     *
     * @param string $name Назва
     * @param mixed $var Змінна
     * @throws Exception Помилка у разі якщо змінна пуста
     */
    public static function isEmpty(string $name, mixed $var)
    {
        $trimmed_var = trim($var ?? "");
        if(empty($trimmed_var)) {
            throw new Exception($name . " is empty!");
        }
    }

    public static function isEmailCorrect(string $email){
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email вказано не вірно!");
        }
    }

    public static function isURLCorrect(string $url){
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception("Посилання вказано не вірно!");
        }
    }
}
