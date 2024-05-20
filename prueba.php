<?php

$datos = array(array("id"=>"1","nombre"=>"juan"),
               array("id"=>"2","nombre"=>"bety"),
               array("id"=>"3","nombre"=>"niña"));
var_dump(array_search("3",$datos[2]));
?>