<html>
  <head>
    <title>Generar Campos</title>
    <style type="text/css">
      .tabla {
        border-top: solid #C1E4FF 1px;
        border-left: solid #C1E4FF 1px;
      }
      .celdaNombreAttr {      
              font-family: Verdana, Arial, Sans;
              font-size: 8pt;
              font-weight: bold;
              text-align: right;
              vertical-align: top;
              color: #022440;
              background-color: #BBCAD6;
     }
     .celdaValorAttr {
           font-family: Verdana, Arial, Sans;
           font-size: 8pt;
           vertical-align: top;
           font-weight: bold;
           color: #4c6082;
           border-bottom: solid #4D87B6 1px;
           border-right: solid #4D87B6 1px;
     }  
    </style>
  </head>
  <body>
<?php
include("funciones.php");
$tabla = $_REQUEST['tabla'];
$bdcon = pg_connect("dbname=regacad" . $authbd);
$SQLtxt = "SELECT * FROM $tabla;";
$resultado = pg_query($bdcon, $SQLtxt);
$cant_campos = pg_num_fields($resultado);
echo("<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>\n");
for ($x=0; $x < $cant_campos; $x++) {
	$nombre_campo = pg_field_name($resultado,$x);
	echo("  <tr>\n");
	echo("    <td class='celdaNombreAttr'>$nombre_campo:</td>\n");	
	echo("    <td class='celdaValorAttr'><input type='text' name='$nombre_campo' value=\"<php> PESO_['" . $nombre_campo . "']; </php>\"></td>\n");	
	echo("  </tr>\n");
}
echo("</table>\n");
?>  
  </body>
</html>