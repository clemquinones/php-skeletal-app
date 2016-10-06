<?php

function dd($var) {
    echo '<pre>', print_r($var, true), '</pre>';
    exit;
}

function d($var) {
    echo '<pre>', print_r($var, true), '</pre>';
}