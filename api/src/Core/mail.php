<?php

class Mail{

    public const TEMPLATE_SIGNUP_ACTIVATION_SUBJECT = "Підтвердження реєстрації";
    public const TEMPLATE_SIGNUP_ACTIVATION_MESSAGE = "Для підтвердження реєстрації - перейдіть за посиланням: %URL%";

    public const TEMPLATE_PRICE_CHANGE_SUBJECT = "На деякі товари у ваших підписках змінилась ціна!";
    public const TEMPLATE_PRICE_CHANGE_MAIL = "Ось, подивiться!<br><br>";

    public static function send(string $email, string $subject, string $message){

        $message = "<html><body>".$message."</body></html>";
        
        return self::sendByBelogubCom($email, $subject, $message);
    }

    private static function sendByBelogubCom($email, $subject, $message){

        // NOTE: ну звичайно ж так робити не можна!
        // По хорошому треба використовувати або дефолтний mail() (який не працює на локалхості без танців с бубнами),
        // або що ще краще - умовний PHPMailer, тобто налаштувати SMTP і через реквізити підключитись.
        // Але оскільки була вимога "не можна використовувати бібліотекі та фреймворки",
        // а мені важливо було щоб тестувальники змогли перевірити навіть запустивши докер на локальній машині
        // (та щоб інструкція не займала більше місця аніж усі файли проекту) - я пішов на таку хитрість.

        $url = 'https://email.belogub.com/';

        $data = [
            "email" => $email,
            "subject" => $subject,
            "message" => $message
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        $bool_response = json_decode($response);
        return $bool_response;
    }
}