<?php

include("funciones.php");

$regiones = consulta_sql("SELECT id,romano AS nombre FROM regiones;");

$texto_buscar = $_REQUEST['texto_buscar'];
$region       = $_REQUEST['region'];
$buscar       = $_REQUEST['buscar'];
$colegios     = array(array());
  
if ($buscar == "Buscar" && ($texto_buscar <> "" OR $region <> "")) {
	$SQLtxt = "SELECT rbd,nombre,comuna,region_romano as region,dependencia FROM vista_colegios WHERE ";
	if ($texto_buscar <> "") {	
		$palabras_buscar = explode(" ", trim($texto_buscar));
		$condiciones = "";		
		for ($x=0;$x<count($palabras_buscar); $x++) {
			$condiciones .= "nombre ~* '" . $palabras_buscar[$x] . "' AND ";
		}
		$condiciones = substr($condiciones,0,strlen($condiciones)-4);
		if ($region > 0) {
			if ($condiciones <> "") { $condiciones .= " AND "; }
			$condiciones .= " region_n_ord=$region";
		}
		$SQLtxt .= "$condiciones ORDER BY nombre;";
		//echo($SQLtxt);
		$colegios = consulta_sql($SQLtxt);
	}		
}
?>
<html>
  <head>
    <title>Buscar colegios - SGU - UMC</title>
    <link href="sgu.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="funciones.js"></script>
  </head>
  <body>
    <div align="center">
      <b>Buscador de colegios</b>
    </div>
    <form action="buscar_colegio.php">
    <table cellpadding="2" cellspacing="1" border="0" class="tabla" align="center">
      <tr>
        <td class="celdaNombreAttr">Buscar por nombre:</td>
        <td class='celdaValorAttr'>
          <input type="text" name="texto_buscar" class="input_texto" value="<?php echo($texto_buscar); ?>">
        </td>
        <td class="celdaNombreAttr">Filtrar por regi&oacute;n:</td>
        <td class='celdaValorAttr'>
          <select name='region'>
            <option value=''>-- Seleccione --</option>
            <?php echo(select($regiones,$_REQUEST['region'])); ?>        
          </select>
        </td>
        <td>
          <input type="submit" name="buscar" value="Buscar" class="input_texto">
        </td>
      </tr>
    </table><br>
    <table cellpadding="2" cellspacing="1" border="0" class="tabla" align="center">
      <tr class='filaTituloTabla'>
		<?php
			if (count($colegios) > 0) {
				foreach ($colegios[0] as $nombre_campo => $valor_campo) {
					$nombre_campo = strtoupper($nombre_campo);
					echo("<td class='tituloTabla'>$nombre_campo</td>\n");
				}
			};
		?>
      </tr>
		<?php
			if (count($colegios) > 0) {			
				for ($x=0; $x<count($colegios); $x++) {
					echo("  <tr class='filaTabla'>\n");
					foreach ($colegios[$x] as $nombre_campo => $valor_campo) {
						echo("<td class='textoTabla'>$valor_campo</td>\n");
					}
				}
			} else {
				$cant_campos = count(count($colegios));
				echo("<td class='textoTabla' colspan='$cant_campos'>"
				     ."  No hay registros para los criterios de b&uacute;squeda/selecci&oacute;n\n"
				     ."</td>\n");
			}
		?>
    </table>
    </form>
</body>
</html>