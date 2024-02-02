<?php

class Mail{

    public const TEMPLATE_SIGNUP_CONFIRMATION_SUBJECT = "Підтвердження реєстрації";
    public const TEMPLATE_SIGNUP_CONFIRMATION_MESSAGE = "Для підтвердження реєстрації - перейдіть за посиланням: %URL%";

    public static function send(string $email, string $subject, string $message){

        $headers = 'From: PandaTeam@belogub.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        
        return mail($email, $subject, $message, $headers, "-fPandaTeam@belogub.com");
    }
}