<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/db.cfg.php';
require_once __DIR__.'/../src/Core/requires.php';

class UserTest extends TestCase
{
    // Мокируем сессию, чтобы тестировать метод getID()
    public function testGetIDSuccess()
    {
        $_SESSION['user_id'] = 1;
        $this->assertEquals(1, User::getID());
    }

    public function testGetIDFail()
    {
        $_SESSION['user_id'] = null;
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Ви не авторизовані!");

        User::getID();
    }

    public function testGetDatal()
    {
        $_SESSION['user_id'] = 1;
        $data = User::getData();
        $this->assertIsArray($data);
    }

    public function testGetDataByEmailSuccess()
    {
        $data = User::getDataByEmail("first@mail.com");
        $this->assertIsArray($data);
    }

    public function testGetDataByEmailFail()
    {
        $data = User::getDataByEmail("first@gmail.com");
        $this->assertFalse($data);
    }

    public function testSignUp()
    {
        $email = time() . "User-SignUp@mail.com";
        $data = User::signUp($email, "password");
        $this->assertIsInt($data);
    }

    public function testSendActivationCode()
    {
        $email = "test@gmail.com";
        $result = User::sendActivationCode($email);

        $this->assertNull($result);
    }

    public function testIsNotActivated()
    {
        $email = "first@gmail.com";
        $code = md5(time() . $email . rand(0, 100000));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Це посилання для активації недійсне.");

        User::isNotActivated($email, $code);
    }

    public function testActivate()
    {
        $email = "first@gmail.com";
        $code = md5(time() . $email . rand(0, 100000));

        $result = User::activate($email, $code);

        $this->assertNull($result);
    }

    public function testSignInSuccess() {
        $email = "first@mail.com";
        $password = "PandaTeam_Test_Pass";

        $result = User::signIn($email, $password);
        $this->assertNull($result);
    }

    public function testSignInFail() {
        $email = "first@mail.com";
        $password = "PandaTeam_Test_Wrong_Pass";

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Ви ввели неправильний email або пароль");

        User::signIn($email, $password);
    }

    public function testCreateFromSubscriptionForm(){
        $email = time() . "User-SignUp-From-Subscription-Form@mail.com";
        $data = User::createFromSubscriptionForm($email);
        $this->assertIsInt($data);
    }

    public function testSignOut() {
        $result = User::signOut();
        $this->assertNull($result);
    }
}
