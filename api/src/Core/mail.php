<?php

class Mail{

    public const TEMPLATE_SIGNUP_ACTIVATION_SUBJECT = "Підтвердження реєстрації";
    public const TEMPLATE_SIGNUP_ACTIVATION_MESSAGE = "Для підтвердження реєстрації - перейдіть за посиланням: %URL%";

    public const TEMPLATE_PRICE_CHANGE_SUBJECT = "На деякі товари у ваших підписках змінилась ціна!";
    public const TEMPLATE_PRICE_CHANGE_MAIL = "Ось, подивiться!<br><br>";

    private const FROM = "PandaTeam@belogub.com";

    public static function send(string $email, string $subject, string $message){

        $message = "<html><body>".$message."</body></html>";
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . self::FROM . "\r\n";
        
        $additional_params = "-f".self::FROM;
        
        return mail($email, $subject, $message, $headers, $additional_params);
    }
}