<?php

function db_connect(){
    include_once './../funciones.php';
    $dbconn = pg_connect("dbname=regacad" . $authbd)
     or die('No se ha podido conectar: ' . pg_last_error());

     return $dbconn;
}