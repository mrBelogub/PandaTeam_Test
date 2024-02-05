<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Результат ТЗ Білогуб С.В.</title>

  <script src="https://code.jquery.com/jquery-latest.min.js"></script>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

  <link rel="stylesheet" href="assets/styles/style.css">
</head>

<body>
  <div class="content">

    <div id="activation-alert"></div>

    <h5>Підписатись на нове оголошення:</h5>
    <div class="input-group mb-3">
      <input id="new-url-input" type="text" class="form-control" placeholder="Вставте посилання на оголошення з OLX" oninput="checkURLInput(this)">
      <button id="new-url-button" class="btn btn-primary" type="button" disabled onclick="subscribeToAdvertisement()">Підписатись</button>
    </div>

    <hr>
    <h5>Ваші підписки:</h5>
    <div id="advertisements"></div>

    <br><br>

    <center><button id="new-url-button" class="btn btn-danger" type="button" onclick="signOut()">Вийти з аккаунту</button></center>

    <hr>

    <center>
      <button id="update-olx-prices" class="btn btn-outline-primary" type="button" onclick="updateOLXPrices()">Примусово оновити*</button>
    </center>
    *Мається на увазі - зімітувати роботу крону, тобто пройтись по всім оголошенням (не лише цього користувача) та оновити ціни де вони змінились (та оповістити про це користувачів).
    Цієї кнопки не має бути, але треба ж дати змогу перевіряючим тестове через фронт примусово пройтись по всім оголошенням

    <div class="modal fade" id="prices-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Історія цін</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="prices-modal-body">
            ...
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрити</button>
          </div>
        </div>
      </div>
    </div>

  </div>

  <script type="text/javascript" src="assets/scripts/script.js?>"></script>

</body>

</html>