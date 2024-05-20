<?php

header("Location: http://www.umcervantes.cl/");

include("funciones.php");

//$nombre_usuario = $_REQUEST['nombre_usuario'];
$id = $_REQUEST['id'];
if ($id == "") {
	header("Location: http://www.umcervantes.cl");
	exit;
}

if (!isset($_SESSION)) {
	header("Location: http://www.umcervantes.cl");
	exit;
}

$nombre_usuario = $_SESSION['login_username'];	
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="es">
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
    <link rel="shortcut icon" href="../img/logo_sgu.ico">
    <title>UMC - SGU - Registro Académico</title>
    <script language="JavaScript1.2" src="../funciones.js"></script>
    <style type="text/css">
      img { border: 0 }
      font {
             font-family: Verdana, Arial, Sans;
           }
      input,select {
              font-family: Verdana, Arial, Sans;
              font-size: 8pt;
              font-weight: bold;
              color: #4c6082;
              border: solid #4D87B6 1px;
              width: 150px;              
            }
      input:focus,select:focus {
              background: #F1F0FF;
              border: solid #4D87B6 1px;
            }
      input:hover,select:hover {
              background: #F1F9FF;
              border: solid #4D87B6 1px;
            }
     .celdaNombreAttr {
              font-family: Verdana, Arial, Sans;
              font-size: 8pt;
              font-weight: bold;
              text-align: right;
              color: #022440;
              background-color: #BBCAD6;
     }
     #tablaPrincipal {
           border: solid #B4DFFF 1px;
           background: #F1F9FF;
           width: 100%;
           font-family: Verdana, Arial, Sans;
           font-size: 10pt;
           color: #4c6082;
     }
     #creditos {
           font-family: Verdana, Arial, Sans;
           font-size: 7pt;
           color: #c5c5c5;
           text-align: center;
     }
    </style>
  </head>
  <body bgcolor="#ffffff" topmargin="5" leftmargin="5" rightmargin="5">
    <table cellpadding="0" cellspacing="1" border="0" align="center" height="100%" id="tablaPrincipal">
      <tr>
        <td align="center" valign="top">
          <img src="../img/logo_UMC-sgu.gif" alt="UMC" title="UMC"><br>
          <b>S</b>istema de <b>G</b>estión <b style="color:#dd312d">U</b>niversitaria<br>
          <b>SG</b><b style="color: #dd312d">U</b><br>
          <sup style="color: #000000"><br>Dirección de Registro Académico<br></sup><br>
          <table>
          <form name="formulario" action="autentificar.php" method="post">
            <tr bgcolor="#4c6082">
              <td colspan="2" align="center">
                <font color="#FFFFFF" size="2"><b>Acceso restringido a Estudiantes UMC</b></font>
              </td>
            </tr>
            <tr>
              <td class="celdaNombreAttr" align="right">Nombre de usuario:</td>
              <td class="celdaValorAttr" ><input type="text" name="nombre_usuario" value="<?php echo($nombre_usuario); ?>"></td>
            </tr>
            <tr>
              <td class="celdaNombreAttr" align="right">R.U.T.:</td>
              <td class="celdaValorAttr" valign="top"><input type="text" name="rut"></td>
            </tr>
            <tr bgcolor="#4c6082">
              <td colspan="2" align="center"><input type="submit" name="entrar" value="Entrar"></td>
            </tr>
          </form>
          </table>
          <br>
          <b>NOTA:</b> Recuerda que debes ingresar tu RUT sin los puntos, por ejemplo: 73124400-6
          <script>
            if (formulario.nombre_usuario.value == "") { formulario.nombre_usuario.focus(); } else { formulario.rut.focus(); }
          </script>
        </td>
      </tr>
      <tr>
        <td id="creditos">
          Sistema de Gesti&oacute;n Universitaria (SGU) tiene licencia GPL 2.0 (http://www.gnu.org).<br>
          Es desarrollado por el Departamento de Inform&aacute;tica en conjunto con la
          Direcci&oacute;n de Registro Acad&eacute;mico de la<br>Universidad Miguel de Cervantes
        </td>
      </tr>
      <tr>
        <td align="center">
          <a href="http://www.postgresql.org"><img src="../img/logo_pg.gif" title="Powered by PostgreSQL" alt="Powered by PostgreSQL"></a>
          <a href="http://www.php.net"><img src="../img/logo_php.png" title="Powered by PHP" alt="Powered by PHP"></a>
          <a href="http://www.apache.org"><img src="../img/logo_apache.gif" title="Powered by Apache 2.0" alt="Powered by Apache 2.0"></a>
        </td>
      </tr>
    </table>    
  </body>
</html>
