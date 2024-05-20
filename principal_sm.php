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
	$modulo = "contenido";
};
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html lang="es">
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
    <link rel="shortcut icon" href="img/logo_sgu.ico">
    <title>UMC - SGU</title>
    <script language="JavaScript1.2" src="funciones.js"></script>
    <script language="JavaScript1.2" src="js/boxover.js"></script>
    <script language="JavaScript1.2" src="js/boxover_span.js"></script>
    <script language="JavaScript1.2" src="js/calendar.js"></script>
    <script language="JavaScript1.2" src="js/separador_de_miles.js"></script>
    <script language="JavaScript1.2" src="js/jquery-1.10.1.min.js"></script>
    <script language="JavaScript1.2" src="js/jquery.fancybox.pack.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />
  <!--  <script language="JavaScript1.2" src="js/jquery.multiple.select.js"></script> -->
    <style>
      @media print {
        @page {page-break-after: always; size: 21.5cm 25cm; }
      }
    </style>
    <link href="sgu.css?date=<?php echo(date("Ymd")); ?>" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="js/jquery.fancybox.css">
<!--    <link rel="stylesheet" type="text/css" href="js/multiple-select.css"> -->
    <link rel="stylesheet" type="text/css" href="js/Kalendae/kalendae.css">
  </head>
  <body bgcolor="#ffffff" topmargin="5" leftmargin="5" rightmargin="5">
    <table cellpadding="2" cellspacing="1" border="0" align="center" bgcolor="#B4DFFF" width="100%" height="100%">
      <tr bgcolor="#F1F9FF">
        <td valign="top">
          <?php include($modulo . ".php"); ?>
        </td>
      </tr>
    </table>    
  </body>
</html>
