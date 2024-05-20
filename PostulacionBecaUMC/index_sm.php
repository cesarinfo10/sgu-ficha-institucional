<?php

include("../funciones.php");
session_start();
setlocale(LC_ALL,"es_ES.UTF8");
setlocale(LC_ALL,"es_ES@euro");
/*if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};*/
$modulo = $_REQUEST['modulo'];
if ($modulo == "") {
	$modulo = "fuasumc_postulaciones_alumno";
}
$enlbase    = "index.php?modulo";
$enlbase_sm = "index_sm.php?modulo";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="es">
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
    <link rel="shortcut icon" href="../img/logo_sgu.ico">
    <title>UMC - SGU - Postulación Beca Socioeconómica</title>
    <script language="JavaScript1.2" src="../funciones.js"></script>
    <link href="../sgu.css" rel="stylesheet" type="text/css">
    <script language="JavaScript1.2" src="../js/separador_de_miles.js"></script>
    <script language="JavaScript1.2" src="../js/jquery-1.10.1.min.js"></script>
    <script language="JavaScript1.2" src="../js/jquery.fancybox.pack.js"></script>
    <link href="sgu.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="../js/jquery.fancybox.css">
  </head>
  <body bgcolor="#ffffff" topmargin="5" leftmargin="5" rightmargin="5">
    <table cellpadding="2" cellspacing="1" border="0" align="center" bgcolor="#B4DFFF" width="100%" height="100%">
      <tr>
        <td align="center" valign="top">
          <table width="100%" cellpadding="1" cellspacing="0" border="0">
            <tr bgcolor="#F1F9FF">
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
