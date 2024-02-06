<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/db.cfg.php';
require_once __DIR__.'/../src/Core/requires.php';


class MailTest extends TestCase {

    public function testMail() {
        
        $sended_mail = Mail::send("test@gmail.com", "subject", "message");

        $this->assertTrue($sended_mail);
    }

}
