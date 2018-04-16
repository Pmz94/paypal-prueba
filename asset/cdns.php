<?php

function getCDN($libreria) {
    if($libreria == 'bootstrap.css') {
        $libreria = 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.0/css/bootstrap.css';
    } else if($libreria == 'jquery.js') {
        $libreria = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js';
    } else if($libreria == 'bootstrap.js') {
        $libreria = 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.0/js/bootstrap.js';
    }
    return $libreria;
}