<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
include("funciones.php");
?>
<html lang="es">
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
    <link rel="shortcut icon" href="img/logo_sgu.ico">
    <title>UMC - SGU</title>
    <script language="JavaScript1.2" src="funciones.js"></script>
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
     }
     #creditos {
           font-family: Verdana, Arial, Sans;
           font-size: 7pt;
           color: #c5c5c5;
           text-align: center;
     }
	 ::placeholder {
		text-align: right;
	 }
    </style>
    <link href="sgu.css" rel="stylesheet" type="text/css">
  </head>
  <body bgcolor="#ffffff" topmargin="15" leftmargin="15" rightmargin="15">
    <table cellpadding="0" cellspacing="1" border="0" align="center" width="100%" height="100%" class="tablaPrincipal">
      <tr>
        <td align="center" valign="top">
	      <br>
          <img src="img/logoumc.png" alt="UMC" title="UMC"><br><br>
          <span class="texto" style="font-variant: small-caps; color: white; text-shadow: black 0.1em 0.1em 0.2em"><b>Sistema de Gesti&oacute;n Universitaria</b></span><br>
          <!-- <font size="2">Direcci&oacute;n de Informática</font><br><br> -->
          <span class="texto"  style="font-size: 18px; color: #4c6082; text-shadow: 0px 1px 4pt #1E90FF"><b>SGU</b></span><br><br>
          <table cellpadding="3" cellspacing="0" border="0" class="tabla" bgcolor="#FFFFFF" style="box-shadow: 1px 5px 10px 5px #B4C9DA;">
          <form name="formulario" action="autentificar.php" method="post" onSubmit="return val_entrada('nombre_usuario','clave','tipo');">
            <tr class='filaTituloTabla'>
              <td colspan="3" style="text-align: center" class="celdaNombreAttr">
                <div  class="tituloModulo">🔐 Acceso Restringido</div>
              </td>
            </tr>
            <tr>
              <td class="celdaNombreAttr" align="right">Nombre de usuario:</td>
              <td class="celdaValorAttr" ><input type="text" name="nombre_usuario" class="boton" placeholder="👤" required></td>
            </tr>
            <tr>
              <td class="celdaNombreAttr" align="right">Contrase&ntilde;a:</td>
              <td class="celdaValorAttr" ><input type="password" name="clave" class="boton" placeholder="🔑" required></td>
            </tr>
            <tr>
              <td class="celdaNombreAttr" align="right">Perfil:</td>
              <td class="celdaValorAttr">
                <select name="tipo" class='filtro' required>
                  <option value="">-- Seleccione --</option>
						<?php echo(select(tipos_usuario(null),null)); ?>
                </select>
              </td>      
            </tr>
            <tr>
              <td class="celdaNombreAttr" style="text-align: center" colspan="2" align="center"><input type="submit" name="entrar" value="Entrar"></td>
            </tr>
          </form>
          </table>
          <br><br>
          <p class='texto' align='center'><a href='https://www.umcervantes.cl/acceso-a-servicios-internet-docentes/' class='boton'><font size='20'>🎓</font><br>Acceso Docentes</a></p>
        </td>
      </tr>
      <tr>
        <td id="creditos">
          Desarrollado por la Dirección de Inform&aacute;tica<br>de la Universidad Miguel de Cervantes
        </td>
      </tr>
      <tr>
        <td align="center">
          <a href="http://www.postgresql.org"><img src="img/logo_pg.gif" title="Powered by PostgreSQL" alt="Powered by PostgreSQL"></a>
          <a href="http://www.php.net"><img src="img/logo_php.png" title="Powered by PHP" alt="Powered by PHP"></a>
          <!-- <a href="http://www.apache.org"><img src="img/logo_apache.gif" title="Powered by Apache 2.0" alt="Powered by Apache 2.0"></a> -->
        </td>
      </tr>
    </table>    
  </body>
</html>
