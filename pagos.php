<!DOCTYPE html>
<html lang = "es">

<head>
    <meta charset = "UTF-8">
    <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
    <meta http-equiv = "X-UA-Compatible" content = "ie=edge">
    <link rel = "shortcut icon" type = "image/x-icon" href = "asset/img/favicon.ico">
    <title>Pagos</title>
    <link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/css/bootstrap.css">
    <link rel = "stylesheet" href = "asset/css/style.css">
</head>

<div class = "container">
    <?php require 'obtenerpagos.php'; ?>
</div>

<body>
    <div class="container text-center">
        <h1><strong>Pagos realizados</strong></h1>
        <table>
            <thead></thead>
            <tbody></tbody>
        </table>
    </div>
</body>

</html>
