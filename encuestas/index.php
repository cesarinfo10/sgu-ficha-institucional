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
	$modulo = "encuestas";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="es">
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
    <link rel="shortcut icon" href="../img/logo_sgu.ico">
    <title>UMC - SGU - Encuestas</title>
    <script language="JavaScript1.2" src="../funciones.js"></script>
    <link href="../sgu.css" rel="stylesheet" type="text/css">
  </head>
  <body bgcolor="#ffffff" topmargin="15" leftmargin="15" rightmargin="15">
    <table cellpadding="2" cellspacing="1" border="0" align="center" bgcolor="#B4DFFF" width="100%" class="tablaPrincipal">
      <tr>
        <td align="center" valign="top">
          <table width="100%" cellpadding="1" cellspacing="0" border="0">
            <tr>
              <td width="100%">
                <?php include("arriba.php"); ?>
              </td>
            </tr>
            <tr>
              <td></td>
            </tr>
            <tr>
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
