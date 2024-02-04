<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реєстрація</title>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="assets/styles/signup.css">
</head>

<body>
    <div class="wrapper">
        <div class="text-center name">
            Реєстрація
        </div>
        <form id="registration-form">
            <div class="form-field d-flex align-items-center">
                <span class="far fa-user"></span>
                <input type="text" name="email" placeholder="Ваш E-mail">
            </div>
            <div class="form-field d-flex align-items-center">
                <span class="fas fa-key"></span>
                <input type="password" name="password" placeholder="Пароль">
            </div>
            <button class="btn">Зареєструватись</button>
        </form>

        <div id="sign-up-alert"></div>

        <div class="text-center has-account">Вже э аккаунт? <a href="signin.php"><u>Авторизуватись!</u></a></div>

        <hr style="border-top: 1px solid rgb(200, 200, 200);">
        <div class="text-center name">
            АБО
        </div>
        <form id="new-subscription-form">
            <span class="">Підписатись без реєстрації:</span>
            <br><br>
            <div class="form-field d-flex align-items-center">
                <input type="text" name="url" placeholder="Посилання на оголошення з OLX">
            </div>
            <div class="form-field d-flex align-items-center">
                <input type="text" name="email" placeholder="Ваш E-mail">
            </div>

            <button class="btn">Підписатись</button>
        </form>

        <div id="new-subscription-alert"></div>
    </div>

    <script type="text/javascript" src="assets/scripts/signup.js"></script>
</body>

</html>