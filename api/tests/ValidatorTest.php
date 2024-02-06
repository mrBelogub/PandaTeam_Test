<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/db.cfg.php';
require_once __DIR__.'/../src/Core/requires.php';

class ValidatorTest extends TestCase
{
    public function testIsEmptyValid()
    {
        // This test should pass without throwing an exception
        $this->expectNotToPerformAssertions();
        Validator::isEmpty("Name", "Some value");
    }

    public function testIsEmptyInvalid()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Name is empty!");

        Validator::isEmpty("Name", "");
    }

    public function testIsEmailCorrectValid()
    {
        // Подготовим данные для теста
        $email = 'valid_email@example.com';

        // Вызовем метод и проверим, что он не выбрасывает исключение
        try {
            Validator::isEmailCorrect($email);
            // Утверждение: исключение не выброшено
            $this->assertTrue(true);
        } catch (Exception $e) {
            // Утверждение: не ожидаем исключение
            $this->fail('Unexpected exception');
        }
    }

    public function testIsEmailCorrectInvalid()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Email вказано не вірно!");

        Validator::isEmailCorrect("invalid_email");
    }

    public function testIsURLCorrectValid()
    {
        // Подготовим данные для теста
        $url = 'http://example.com';

        // Вызовем метод и проверим, что он не выбрасывает исключение
        try {
            Validator::isURLCorrect($url);
            // Утверждение: исключение не выброшено
            $this->assertTrue(true);
        } catch (Exception $e) {
            // Утверждение: не ожидаем исключение
            $this->fail('Unexpected exception');
        }
    }

    public function testIsURLCorrectInvalid()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Посилання вказано не вірно!");

        Validator::isURLCorrect("invalid_url");
    }

    // Add more test methods for different scenarios
}
