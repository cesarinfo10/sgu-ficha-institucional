<?php

include("funciones.php");

$usuario = $_REQUEST['nombre_usuario'];
$clave   = $_REQUEST['clave'];
$tipo    = $_REQUEST['tipo'];
$entrar  = $_REQUEST['entrar'];

$modulo     = "autentificar";
$accion_log = $_SERVER['REQUEST_URI'];

$tipos_usuarios = tipos_usuario($tipo);
$servidor = $tipos_usuario['servidor'];

if ($entrar == '') {
	header("Location: index.php");
};

if ($tipo==3) { $clave = urldecode($clave); }

$auth_bd = auth_bd($usuario,$tipo);

if ($auth_bd) {
	$servidor = tipos_usuario($tipo);
	$servidor = $servidor['servidor'];
	error_reporting(0);
	$auth_passwd = auth_imap($usuario,$clave,$servidor);
	if ($auth_passwd) {
		session_start();
		$_SESSION['autentificado'] = true;
		$_SESSION['usuario']       = $usuario;
		$_SESSION['tipo']          = $tipo;
		$_SESSION['id_usuario']    = id_usuario($usuario,$tipo);
		$_SESSION['id_escuela']    = escuela_usuario(id_usuario($usuario,$tipo));
		$_SESSION['id_unidad']     = unidad_usuario(id_usuario($usuario,$tipo));
		$_SESSION['ids_carreras']  = ids_carreras_escuela($_SESSION['id_escuela']);		
		$_SESSION['enlace_volver'] = "";
		$mensaje_log = "Entrando desde=" . $_SERVER['REMOTE_ADDR'];
		log_sgu($accion_log,$usuario,$mensaje_log,$modulo);
		header("Location: principal.php");
		exit;
	};
} else {
	$mensaje_log = "ERROR: Usuario inválido en BD o no está activo o no corresponde el tipo: desde " . $_SERVER['REMOTE_ADDR'];
	log_sgu($accion_log,$usuario,$mensaje_log,$modulo);
	header("Location: error_ingreso.php");
	exit;	
};	
$accion_log = "ERROR";
$mensaje_log = "Contraseña incorrecta o no existe en $servidor: desde " . $_SERVER['REMOTE_HOST'] . "(" . $_SERVER['REMOTE_ADDR'] . ")";
log_sgu($accion_log,$usuario,$mensaje_log,$modulo);
header("Location: error_ingreso.php");
?>
