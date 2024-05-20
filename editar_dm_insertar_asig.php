<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_malla       = $_REQUEST['id_malla'];
$linea_tematica = $_REQUEST['linea_tematica'];
$nivel          = $_REQUEST['nivel'];

if (!is_numeric($id_malla)) {
	echo(js("location.href='$enlbase=editar_detalle_malla&id_malla=$id_malla'"));
	exit;
}

$bdcon = pg_connect("dbname=regacad" . $authbd);

$SQLtxt = "SELECT id,ano AS \"año\",carrera,niveles,id_escuela,id_carrera,cant_asig_oblig
           FROM vista_mallas WHERE id=$id_malla;";
$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
if ($filas > 0) {
	$malla = pg_fetch_all($resultado);
	$cant_asig_oblig = $malla[0]['cant_asig_oblig'];
	$niveles         = $malla[0]['niveles'];
	$id_carrera      = $malla[0]['id_carrera'];
	$id_escuela      = $malla[0]['id_escuela'];
};

if ($_REQUEST['insertar'] == "Insertar") {
	$SQLtxt0 = "SELECT id FROM detalle_mallas WHERE id_malla=$id_malla AND caracter=1;";
	$resultado0 = pg_query($bdcon, $SQLtxt0);
	$filas0 = pg_numrows($resultado0);
	if ($filas0 >= $cant_asig_oblig && $_REQUEST['caracter'] == 1) {
		$mensaje  = "Se han definido $cant_asig_oblig asignaturas obligatorias ";
		$mensaje .= "y actualmente hay $filas0 asignaturas en esta malla\\nNo se insertará.";
		echo(msje_js($mensaje));
		echo(js("location.href='$enlbase=editar_detalle_malla&id_malla=$id_malla';"));
		exit;
	} else {
		$aCampos = array("id_malla","id_prog_asig","caracter","nivel","linea_tematica");	
		$SQLinsert = "INSERT INTO detalle_mallas " . arr2sqlinsert($_REQUEST,$aCampos);
		$resultado = pg_query($bdcon, $SQLinsert);
		if (!$resultado) {
			echo(msje_js(pg_last_error()));
		} else {
			$filas = pg_affected_rows($resultado);
		};
		if ($filas > 0) {
			echo(msje_js("Se ha insertado la asignatura definida a esta malla"));
			echo(js("location.href='$enlbase=editar_detalle_malla&id_malla=$id_malla';"));
			exit;
		};
	};
};

if ($filas > 0) {
	$aNiveles = array();	
	for($x=1;$x<=$niveles;$x++) {
		$aNiveles = array_merge($aNiveles,array(array('id'=>$x,'nombre'=>$x)));
	};
	
	$SQLtxt2 = "SELECT id,ano || '/' || cod_asignatura ||' '|| asignatura AS nombre 
	            FROM vista_prog_asig 
	            WHERE id NOT IN (SELECT id_prog_asig 
	                             FROM detalle_mallas
	                             WHERE id_malla=$id_malla)
	                  AND id_carrera = $id_carrera OR id_carrera IS NULL
	            ORDER BY ano DESC,cod_asignatura;";
	
	$SQLtxt21 = "SELECT id,ano || '/' || cod_asignatura ||' '|| asignatura AS nombre 
	            FROM vista_prog_asig 
	            WHERE id NOT IN (SELECT id_prog_asig 
	                             FROM detalle_mallas
	                             WHERE id_malla=$id_malla)
	                  AND id_carrera <> $id_carrera
	            ORDER BY ano DESC,cod_asignatura;";
	
	$resultado2  = pg_query($bdcon, $SQLtxt2);
	$resultado21 = pg_query($bdcon, $SQLtxt21);
	$filas2  = pg_numrows($resultado2);
	$filas21 = pg_numrows($resultado21);
	$prog_asig2 = array();
	if ($filas2 > 0) {
		$prog_asig2 = pg_fetch_all($resultado2);
	};
	if ($filas21 > 0) {
		$prog_asig21 = pg_fetch_all($resultado21);
	};
	if ($filas2 == 0 && $filas21 == 0) {
		echo(msje_js("No hay asignaturas con sus respectivos programas de estudios disponibles"));
		echo(js("location.href='$enlbase=editar_detalle_malla&id_malla=$id_malla';"));
		exit;
	};
	$prog_asig = array_merge($prog_asig2,$prog_asig21);	
	
	$SQLtxt3 = "SELECT id,nombre FROM lineas_tematicas WHERE id_escuela=$id_escuela;";
	$resultado3 = pg_query($bdcon, $SQLtxt3);
	$filas3 = pg_numrows($resultado3);
	if ($filas3 > 0) {
		$lineas_tematicas = pg_fetch_all($resultado3);
	};
	
	$SQLtxt4 = "SELECT id,nombre FROM caracter_asig;";
	$resultado4 = pg_query($bdcon, $SQLtxt4);
	$filas4 = pg_numrows($resultado4);
	if ($filas4 > 0) {
		$caracter_asig = pg_fetch_all($resultado4);
	};
}

if (empty($_REQUEST['caracter'])) { $_REQUEST['caracter'] = 1; }
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('id_prog_asig','caracter');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_malla" value="<?php echo($id_malla); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($malla[0]['carrera']); ?> - <?php echo($malla[0]['año']); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla">
      <input type="submit" name="insertar" value="Insertar">
    </td>
    <td class="tituloTabla">
      <input type="button" name="cancelar" value="Cancelar"  onClick="cancelar_guardar();">
    </td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width='50%'>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr'>
      <select name='id_prog_asig' id='id_prog_asig' onChange="cambiado();" style="font-weight:normal">
        <option value=''>-- Seleccione --</option>
        <?php echo(select($prog_asig,$_REQUEST['id_prog_asig'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Caracter:</td>
    <td class='celdaValorAttr'>
      <select name='caracter' onChange="cambiado();">
        <option value=''>-- Seleccione --</option>
        <?php echo(select($caracter_asig,$_REQUEST['caracter'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nivel:</td>
    <td class='celdaValorAttr'>
      <select name='nivel' onChange="cambiado();">
        <?php echo(select($aNiveles,$nivel)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>L&iacute;nea Tem&aacute;tica:</td>
    <td class='celdaValorAttr'>
      <select name='linea_tematica' onChange="cambiado();">
        <?php echo(select($lineas_tematicas,$linea_tematica)); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

<script>
  $(document).ready(function () {
      $('#id_prog_asig').selectize({
          sortField: 'text'
      });
  });

</script>