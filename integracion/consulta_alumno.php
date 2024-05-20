<?php

include("../funciones.php");

$cliente_alumno = new SoapClient("https://api.manager.cl/sec/prod/clienteproveedor.asmx?WSDL",array('trace' => 1));

$token = "gD7G3UkDK1E2woKQazzsG19ks39zdU6K43q9u62g8M1kyCZcq3";
$rut_empresa = "73124400-6";

$opts = array("ssl" => array("ciphers"=>'RC4-SHA', "verify_peer"=>false, "verify_peer_name"=>false));

$params = array ("encoding" => 'UTF-8', 
                 "verifypeer" => false, 
                 "verifyhost" => false,
                 "soap_version" => SOAP_1_2, 
                 "trace" => 1, 
                 "exceptions" => 1, 
                 "connection_timeout" => 180, 
                 "stream_context" => stream_context_create($opts));
                 
$alumno = $cliente_alumno -> ObtenerCliente2(array("rutEmpresa" => $rut_empresa,
                                                   "token"      => $token,
                                                   "rutCliente" => "9386444-1"));

var_dump($alumno);

?>
