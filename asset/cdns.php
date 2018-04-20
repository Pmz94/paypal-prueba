<?php

function getcss() {
    echo '
        <link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.0/css/bootstrap.css">
        <link rel = "stylesheet" href = "https://cdn.datatables.net/1.10.16/css/jquery.dataTables.css">
    ';
}

function getjs() {
    echo '
        <script src = "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
        <script src = "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.0/js/bootstrap.js"></script>
        <script src = "https://cdn.datatables.net/1.10.16/js/jquery.dataTables.js"></script>
    ';
}