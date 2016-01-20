<?php
define('API_URL','http://192.168.32.10/myutility/api/');
function connect_db($db = 'local') {
    switch($db){
        case 'merchant_app' :
            try{
                $connection = new mysqli('127.0.0.1', 'root', 'pass', 'shorten');
                return $connection;
            }catch(Exception $e){
                echo $e->getMessage();
            }
            break;
    }
}