<?php

include("funciones.php");
$_SESSION['autentificado'] = true;

$usuario = $_REQUEST['login_name'];
$cod_ch  = $_REQUEST['cod_ch'];
$tipo    = $_REQUEST['t'];

if ($usuario == '' || $cod_ch == '' || $tipo == '') {
	header("Location: index.php");
}

header("Content-Type: text/html; charset=UTF-8");
if ($_REQUEST['cambiar'] == "Cambiar" && $_REQUEST['clave'] <> "" && $_REQUEST['verificar_clave'] <> "") {

	$problemas = false;
	
	if (strlen($_REQUEST['clave']) < 6 || strpos($_REQUEST['clave']," ") > 0) {
		echo(msje_js("ERROR: La contraseña tiene menos de 6 caracteres o bien posee uno o más espacios.\\n\\n"
		            ."Debe ingresar una contraseña distinta"));
		$problemas = true;
	}
	
	if ($_REQUEST['clave'] <> $_REQUEST['verificar_clave']) {
		echo(msje_js("ERROR: Las contraseñas no coinciden. Debe ingresar la misma contraseña en ambos recuadros"));
		$problemas = true;
	}

	if (!$problemas) {
		$clave_encriptada = crypt($_REQUEST['clave']);
		
		$SQL_profesor_pw = "UPDATE shadow 
		                    SET enc_password='$clave_encriptada',newtok=false
		                    WHERE login_name='$usuario'";
		$bdcon = pg_connect("dbname=auth_profesores" . $authbd);
		if (pg_affected_rows(pg_query($bdcon, $SQL_profesor_pw)) > 0) {
			echo(msje_js("La nueva contraseña se ha establecido exitósamente.\\n\\n"
			            ."Desde ahora puede usar su cuenta ($usuario) con esta nueva contraseña"));
			echo(js("location.href='http://www.umcervantes.cl/';"));
			exit;
		} else {
			echo(msje_js("ERROR: No ha sido posible establecer la contraseña ingresada.\\n\\n"
			            ."Por favor inténtelo nuevamente.\\n\\n"
						."Si persiste el error, por favor comuníquese con el Departamento de informática"));
			//echo(js("location.href='http://ugs.umcervantes.cl/sgu/passwd_ch.php?$login_name=$usuario&cod_ch=$cod_ch&t=$tipo';"));
		}
	}
}

if ($tipo = 'pr') {
	$profesor = consulta_sql("SELECT * FROM vista_profesores WHERE nombre_usuario='$usuario'");
	if (count($profesor) > 0) {
		extract($profesor[0]);
		$bdcon = pg_connect("dbname=auth_profesores" . $authbd);
		$SQL_profesor_pw = "SELECT md5(login_name||date_newtok)
							FROM shadow
							WHERE login_name = '$usuario' AND date_newtok+'3 days'>=now() AND newtok";
		$profesor_pw     = pg_fetch_all(pg_query($bdcon, $SQL_profesor_pw));
		if ($cod_ch <> $profesor_pw[0]['md5']) {
			echo(msje_js("ERROR: Código inválido o solicitud fuera de Plazo.\\n\\n "
			            ."Debe solicitar en su Escuela un nuevo enlace (como el "
			            ."que recibió por correo electrónico) para crear su contraseña."));
			echo(js("location.href='http://www.umcervantes.cl';"));
			exit;
		}
	}
}

?>

<html lang="es">
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
    <link rel="shortcut icon" href="img/logo_sgu.ico">
    <title>UMC - SGU - Registro Acad&eacute;mico</title>
    <script language="JavaScript1.2" src="funciones.js"></script>
    <script language="JavaScript1.2" src="js/boxover.js"></script>
    <script language="JavaScript1.2" src="js/boxover_span.js"></script>
    <script language="JavaScript1.2" src="js/calendar.js"></script>
    <script language="JavaScript1.2" src="js/separador_de_miles.js"></script>
    <link href="sgu.css" rel="stylesheet" type="text/css">
  </head>
  <body bgcolor="#ffffff" topmargin="5" leftmargin="5" rightmargin="5">
    <table cellpadding="2" cellspacing="1" border="0" align="center" bgcolor="#B4DFFF" width="100%">
      <tr bgcolor="#F1F9FF">
        <td align="center" valign="top">
        <form name="formulario" action="passwd_ch.php" method="post" onSubmit="return val_entrada('clave','verificar_clave');">
        <input type="hidden" name="cod_ch" value="<?php echo($cod_ch); ?>">
        <input type="hidden" name="login_name" value="<?php echo($usuario); ?>">
        <input type="hidden" name="t" value="<?php echo($tipo); ?>">
          <table width="100%" cellpadding="1" cellspacing="0" border="0">
            <tr>
              <td width="100%">
                <?php include("arriba.php"); ?>
              </td>
            </tr>
            <tr>
              <td width="100%" bgcolor="#ffffff">
                
              </td>
            </tr>
            <tr>
              <td valign="top" class="texto">
                Estimado(a) Profesor(a),<br>
                <br>
                Ahora ingrese una nueva contraseña para su cuenta.<br>
                <br>
				<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
					<tr>
					  <td class='celdaNombreAttr' colspan="2" style='text-align: center'><h3>Establecer Nueva Contraseña</h3></td>
					</tr>
					<tr>
					  <td class='celdaNombreAttr' align='right'>Su nombre:</td>
					  <td class='celdaValorAttr'><?php echo($nombre); ?></td>
					</tr>
					<tr>
					  <td class='celdaNombreAttr' align='right'><h2>Nombre de Usuario:</h2></td>
					  <td class='celdaValorAttr' bgcolor='#FFFF00'><h2><?php echo($nombre_usuario); ?></h2></td>
					</tr>
					<tr>
					  <td class='celdaNombreAttr' align='right'>Nueva contraseña:</td>
					  <td class='celdaValorAttr'>
					    <input type="password" name="clave"><br>
					    Ingrese una contraseña de al menos 6 caracteres, sin espacios, que contengan letras y números
					  </td>
					</tr>
					<tr>
					  <td class='celdaNombreAttr' align='right'>Verifique la nueva contraseña:</td>
					  <td class='celdaValorAttr'>
					    <input type="password" name="verificar_clave"><br>
					    Ingrese nuevamente la misma clave que ingreso en el recuadro anterior					    
					  </td>
					</tr>
					<tr>
					  <td class='celdaNombreAttr' colspan="2" align='right'><input type="submit" name="cambiar" value="Cambiar"></td>
					</tr>
				</table>
				<br><br><br>               
              </td>
            </tr>
            <tr>
              <td id="creditos">
                Sistema de Gesti&oacute;n Universitaria (SGU) tiene licencia GPL 2.0 o superior (http://www.gnu.org).<br>
                Es desarrollado por el Departamento de Inform&aacute;tica en conjunto con la
                Direcci&oacute;n de Registro Acad&eacute;mico de la<br>Universidad Miguel de Cervantes
              </td>
            </tr>
          </table>
        </form>
        </td>
      </tr>
    </table>    
  </body>
</html>
