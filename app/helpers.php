<?php

if(!function_exists('debug')) {
    define('PAGE_SIZE', 100);
    define('DATE_FORMAT', 'Y-m-d H:i:s'); // Y-m-d\TH:i:s.000000\Z');
    function debug($data) {
        var_dump($data);
    }
    function random_date() {
        return strtotime('-'.rand(0, 2160).' hours');
    }
}