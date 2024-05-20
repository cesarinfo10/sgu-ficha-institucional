<?php
include("funciones.php");
session_start();
setlocale(LC_ALL,"es_ES.UTF8");
setlocale(LC_ALL,"es_ES@euro");
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};
$modulo = $_REQUEST['modulo'];
if ($modulo == "") {
	$modulo = "portada";
};
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="es">
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
    <link rel="shortcut icon" href="img/logo_sgu.ico">
    <title>UMC - SGU - Registro Acad&eacute;mico</title>
    <script language="JavaScript1.2" src="funciones.js"></script>
    <script language="JavaScript1.2" src="../js/boxover.js"></script>
    <script language="JavaScript1.2" src="../js/jquery-1.10.1.min.js"></script>
    <script language="JavaScript1.2" src="../js/jquery.fancybox.pack.js"></script>
    <link rel="stylesheet" type="text/css" href="../js/jquery.fancybox.css">
    <link href="sgu.css" rel="stylesheet" type="text/css">
  </head>
  <body bgcolor="#ffffff" topmargin="5" leftmargin="5" rightmargin="5">
    <table cellpadding="2" cellspacing="1" border="0" align="center" bgcolor="#B4DFFF" width="100%">
      <tr bgcolor="#F1F9FF">
        <td align="center" valign="top">
          <table width="100%" cellpadding="1" cellspacing="0" border="0">
            <tr>
              <td colspan="3" width="100%">
                <?php include("arriba.php"); ?>
              </td>
            </tr>
            <tr>
              <td colspan="3" width="100%">
                <?php include("mensaje.php"); ?>
              </td>
            </tr>
            <tr>
              <td valign="top">
                <?php include("menu.php"); ?>
              </td>
              <td width="2" valign="top"></td>
              <td width="100%" valign="top">
                <?php include($modulo . ".php"); ?>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>    
  </body>
</html>