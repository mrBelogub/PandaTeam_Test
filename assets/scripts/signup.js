$('#registration-form').submit(function (event) {

    $('#sign-up-alert').html("");

    event.preventDefault();

    var formData = $(this).serialize();

    $.ajax({
        type: 'POST',
        url: 'api/signUp',
        data: formData,
        success: function (response) {
            $('#sign-up-alert').html(`<div class="alert alert-success">Готово! <a href="signin.php"><u>Перейти до авторизації!</u></a></div>`);
        },
        error: function (error) {
            $('#sign-up-alert').html(`<div class="alert alert-danger">` + error.responseText + `</div>`);

        }
    });

});

$('#new-subscription-form').submit(function (event) {

    $('#new-subscription-alert').html("");

    event.preventDefault();

    var formData = $(this).serialize();

    $.ajax({
        type: 'POST',
        url: 'api/subscribeWithoutAuth',
        data: formData,
        success: function (response) {
            $('#new-subscription-alert').html(`<div class="alert alert-success">Ви успішно підписались!<br><br>Вам на E-mail будуть приходити сповіщення про зміну ціни.<br><br>Також Ви можете відстежувати ціну у особистому кабінеті, авторизувавшись <a href="signin.php"><u>за цим посиланням</u></a>.<br><br><b>(Якщо Ви не реєструвались - використовуйте E-mail у якості пароля)</b></div>`);
        },
        error: function (error) {
            $('#new-subscription-alert').html(`<div class="alert alert-danger">` + error.responseText + `</div>`);
        }
    });

});