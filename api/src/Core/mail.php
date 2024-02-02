<?php

class Mail{

    public const TEMPLATE_SIGNUP_CONFIRMATION_SUBJECT = "Підтвердження реєстрації";
    public const TEMPLATE_SIGNUP_CONFIRMATION_MESSAGE = "Для підтвердження реєстрації - перейдіть за посиланням: %URL%";

    public const TEMPLATE_PRICE_CHANGE_SUBJECT = "На деякі товари у ваших підписках змінилась ціна!";
    public const TEMPLATE_PRICE_CHANGE_MAIL = "Ось, подивiться!<br><br>";

    public static function send(string $email, string $subject, string $message){

        $headers = 'From: PandaTeam@belogub.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        
        return mail($email, $subject, $message, $headers, "-fPandaTeam@belogub.com");
    }
}