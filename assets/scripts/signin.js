$('#authorization-form').submit(function (event) {

    $('#sign-in-alert').html("");

    event.preventDefault();

    var formData = $(this).serialize();

    $.ajax({
        type: 'POST',
        url: 'api/signIn',
        data: formData,
        success: function (response) {
            window.location.href = "index.php";
        },
        error: function (error) {
            $('#sign-in-alert').html(`<div class="alert alert-danger">` + error.responseText + `</div>`);
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
            $('#new-subscription-alert').html(`<div class="alert alert-success">Ви успішно підписались!<br><br>Вам на E-mail будуть приходити сповіщення про зміну ціни.<br><br>Також Ви можете відстежувати ціну у особистому кабінеті, авторизувавшись у формі вище. <br><br><b>(Якщо Ви не реєструвались - використовуйте E-mail у якості пароля)</b></div>`);
        },
        error: function (error) {
            $('#new-subscription-alert').html(`<div class="alert alert-danger">` + error.responseText + `</div>`);
        }
    });

});