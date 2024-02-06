<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/db.cfg.php';
require_once __DIR__.'/../src/Core/requires.php';

class DBTest extends TestCase {

    public function testExecRequest() {
        // Подготовим данные для теста
        $sql = "SELECT * FROM users WHERE id IN (:ids)";
        $params = ['ids' => [1,2,3]];

        // Вызовем метод и проверим, что он возвращает массив
        $result = DB::execRequest($sql, $params);

        $this->assertIsArray($result);
    }

    public function testGetOne() {
        // Подготовим данные для теста
        $sql = "SELECT * FROM users WHERE id = :id";
        $params = ["id" => 1];

        // Вызовем метод и проверим, что он возвращает массив
        $result = DB::getOne($sql, $params);

        $this->assertIsArray($result);
    }

    public function testInsert() {
        // Подготовим данные для теста

        $email = time()."DB-Insert@mail.com";
        $password_hash = password_hash($email, PASSWORD_DEFAULT);

        $sql = "INSERT INTO `users` (`email`, `password_hash`) VALUES (:email, :password_hash);";
        $params = ["email" => $email, "password_hash" => $password_hash];

        // Вызовем метод и проверим, что он возвращает массив
        $result = DB::insert($sql, $params);

        $this->assertIsInt($result);
    }
}
