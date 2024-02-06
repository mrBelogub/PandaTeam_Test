<?php

/**
 * Клас для роботи з поштою
 */
class Mail
{
    /**
     * Шаблон теми листа для підтвердження реєстрації
     */
    public const TEMPLATE_SIGNUP_ACTIVATION_SUBJECT = "Підтвердження реєстрації";
    /**
     * Шаблон тексту листа для підтвердження реєстрації
     */
    public const TEMPLATE_SIGNUP_ACTIVATION_MESSAGE = "Для підтвердження реєстрації - перейдіть за посиланням: %URL%";

    /**
     * Шаблон теми листа для оповіщення про зміну цін
     */
    public const TEMPLATE_PRICE_CHANGE_SUBJECT = "На деякі товари у ваших підписках змінилась ціна!";
    /**
     * Шаблон тексту листа для оповіщення про зміну цін
     */
    public const TEMPLATE_PRICE_CHANGE_MAIL = "Ось, подивiться!<br><br>";


    /**
     * Відправка листа
     *
     * @param string $email E-mail
     * @param string $subject Тема
     * @param string $message Текст
     * @return boolean Статус відправки листа
     */
    public static function send(string $email, string $subject, string $message): bool
    {
        // Додаємо листа до HTML обгортки
        $message = "<html><body>" . $message . "</body></html>";

        // NOTE: в реальних проектах краще використовувати шаблони та створювати готові листи до того як вони будут передані у метод відправки

        // Відправляємо листа та повертаємо результат
        return self::sendByBelogubCom($email, $subject, $message);
    }

    /**
     * Відправка листа через сервер belogub.com
     *
     * @param string $email E-mail
     * @param string $subject Тема
     * @param string $message Текст
     * @return boolean Статус відправки листа
     */
    private static function sendByBelogubCom(string $email, string $subject, string $message): bool
    {
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
