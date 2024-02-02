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
    public static function isEmpty(string $name, $var)
    {
        $trimmed_var = trim($var);
        if(empty($trimmed_var)) {
            throw new Exception($name . " is empty!");
        }
    }
}
