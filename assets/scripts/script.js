getAllSubscriptions();

function getAllSubscriptions() {

    $.get("api/getAllSubscriptions", {}, function (data) {

        var json_data = jQuery.parseJSON(data);

        var is_not_activated = json_data['is_not_activated'];

        if (is_not_activated) {
            $("#activation-alert").html(` <div class="alert alert-danger">
            Увага! Ваш аккаунт не активовано! На реальному проекті це б означало, що ви б не могли користуватись фукціями сайту (або навіть авторизуватись).<br><br>
            Але зараз оскільки це умова із розділу "Ускладення" - то це ні на що не впливає.<br><br>
            Щоб прибрати це повідомленя (тобто активувати аккаунт) - будь ласка, перейдіть за посиланням у надісланому Вам E-mail.<br><br>
            Якщо листа нема у папці "Inbox" - перевірте папку "Spam", або <a href="javascript:void(0);" onclick="resendActivationCode()"><u>натиснiть сюди</u></a> щоб отримати нового листа.
          </div>`);
        }

        var subscriptions = json_data['subscriptions'];

        var advertisements = "";

        $.each(subscriptions, function (key, value) {
            var id = value["id"];
            var title = value["title"];
            var price = value["price"];
            var previous_price = value["previous_price"];
            var updated_at = value["updated_at"];

            var difference_str = "";
            var price_status = "equal";

            if (previous_price !== null) {
                var clean_price = getCleanPrice(price);
                var clean_previous_price = getCleanPrice(previous_price);

                difference = (clean_price - clean_previous_price).toFixed(2);

                if (difference < 0) {
                    var price_status = "down";

                } else if (difference > 0) {
                    difference = "+" + difference;
                    var price_status = "up";
                }

                difference_str = ` <span class="difference_str">(` + difference + `)</span>`;
            }

            var formatted_updated_at = formatDateTime(updated_at);
            var updated_date = formatted_updated_at.date;
            var updated_time = formatted_updated_at.time;

            advertisements += `<div class="advertisement"  onclick="show_details(` + id + `)">
                                    <div class="title">`+ title + `</div>
                                    <div class="price `+ price_status + `">` + price + difference_str + `</div>
                                    <div class="updated_at">
                                        <span class="date">` + updated_date + `</span>
                                        <span class="time">`+ updated_time + `</span>
                                    </div>
                                </div>`;
        });

        $("#advertisements").html(advertisements);

    });
}

function resendActivationCode(){
    $.ajax({
        type: "POST",
        url: "api/resendActivationCode",
        success: function () {
            alert("Код надіслано повторно");
        },
        error: function (jqXHR) {
            alert(jqXHR.responseText);
        }
    });
}

function show_details(id) {
    $.get("api/getAdvertisementPrices?id=" + id, {}, function (data) {
        var json_data = jQuery.parseJSON(data);


        var prices_tr = "";

        var previous_price = 0;

        $.each(json_data, function (key, value) {
            var price = value["price"];
            var created_at = value["created_at"];

            var formatted_created_at = formatDateTime(created_at);
            var created_date = formatted_created_at.date;
            var created_time = formatted_created_at.time;

            var formatted_created_datetime = created_date + " " + created_time;

            var price_status = "equal";
            var difference = "-";

            if (previous_price != 0) {
                var clean_price = getCleanPrice(price);
                var clean_previous_price = getCleanPrice(previous_price);

                difference = (clean_price - clean_previous_price).toFixed(2);

                if (difference < 0) {
                    var price_status = "down";

                } else if (difference > 0) {
                    difference = "+" + difference;
                    var price_status = "up";
                }
            }

            prices_tr = `<tr>
                            <td>`+ formatted_created_datetime + `</td>
                            <td class="`+ price_status + `">` + price + `</td>
                            <td class="`+ price_status + `">` + difference + `</td>
                        </tr>`+ prices_tr;

            previous_price = price;
        });

        var result_table = `<table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Дата</th>
                                        <th scope="col">Ціна</th>
                                        <th scope="col">Зміна ціни</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    `+ prices_tr + `
                                </tbody>
                                </table>`;



        $("#prices-modal-body").html(result_table);
        $("#prices-modal").modal('show');
    });
}


function getCleanPrice(stringed_price) {
    var match = stringed_price.match(/[\d\s.]+(?=\D|$)/);
    var numeric_price = parseFloat(match[0].replace(/\s/g, ''));

    return numeric_price;
}

function checkURLInput(inputElement) {
    var subscribeButton = document.getElementById('new-url-button');

    if (inputElement.value.trim() !== '') {
        subscribeButton.disabled = false;
    } else {
        subscribeButton.disabled = true;
    }
}

function subscribeToAdvertisement() {
    var url = document.getElementById('new-url-input').value.trim();

    $.ajax({
        type: "POST",
        url: "api/subscribeToAdvertisement",
        data: {
            "url": url,
        },
        success: function () {
            alert("Підписка оформлена!");
            document.getElementById('new-url-input').value = "";
            document.getElementById('new-url-button').disabled = true;
            getAllSubscriptions();
        },
        error: function (jqXHR) {
            alert(jqXHR.responseText);
        }
    });
}

function formatDateTime(dateTimeString) {
    let dateTime = new Date(dateTimeString);

    let day = dateTime.getDate().toString().padStart(2, '0');
    let month = (dateTime.getMonth() + 1).toString().padStart(2, '0');
    let year = dateTime.getFullYear();

    let formattedDate = `${day}.${month}.${year}`;

    let hours = dateTime.getHours().toString().padStart(2, '0');
    let minutes = dateTime.getMinutes().toString().padStart(2, '0');

    let formattedTime = `${hours}:${minutes}`;

    return {
        date: formattedDate,
        time: formattedTime
    };
}


function signOut() {

    $.ajax({
        type: "GET",
        url: "api/signOut",
        success: function () {
            window.location.href = "signin.php";
        },
        error: function (jqXHR) {
            alert(jqXHR.responseText);
        }
    });
}

function updateOLXPrices(){
    // NOTE: Цієї функції не має бути, але треба ж дати змогу перевіряючим тестове через фронт приумсово пройтись по всім оголошенням

    $("#update-olx-prices").html("Оновлення (може заняти деякий час)");
    $("#update-olx-prices").prop('disabled', true);

    $.ajax({
        type: "GET",
        url: "api/src/Core/update_prices.php",
        success: function () {
            getAllSubscriptions();
            alert("Оновлення закінчено");
            $("#update-olx-prices").html("Примусово оновити*");
            $("#update-olx-prices").prop('disabled', false);
        },
        error: function (jqXHR) {
            alert(jqXHR.responseText);
            $("#update-olx-prices").html("Примусово оновити*");
            $("#update-olx-prices").prop('disabled', false);
        }
    });

}