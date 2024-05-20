<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_dm = $_REQUEST['id_dm'];

if (!is_numeric($id_dm)) {
	echo(js("location.href='$enlbase=editar_detalle_malla&id_malla=$id_malla'"));
	exit;
};

$requisitos_tipo = array(1 => array(nombre => "Pre requisito:",
                                    campo  => "pre_requisito"),
                         2 => array(nombre => "Pre requisito de:",
                                    campo  => "post_requisito"));                         

$bdcon = pg_connect("dbname=regacad" . $authbd);

$SQLtxt = "SELECT * FROM detalle_mallas WHERE id=$id_dm";
$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
if ($filas > 0) {
	$detalle_malla = pg_fetch_all($resultado);
	$id_malla = $detalle_malla[0]['id_malla'];
	$nivel = $detalle_malla[0]['nivel'];
};

if ($_REQUEST['eliminar'] == "Eliminar asignatura") {
	$SQLdelete = "DELETE FROM detalle_mallas WHERE id=$id_dm";
	$resultado = pg_query($bdcon, $SQLdelete);
	if (!$resultado) {
		echo(msje_js(pg_last_error()));
	} else {
		$filas = pg_affected_rows($resultado);
	};
	if ($filas > 0) {
		echo(msje_js("Se ha borrado la asignatura de esta malla"));
		echo(js("location.href='$enlbase=editar_detalle_malla&id_malla=$id_malla';"));
		exit;
	};
};

if ($_REQUEST['guardar'] == "Guardar") {
	$aCampos = array("id_prog_asig","nivel","caracter","ofertable","linea_tematica");	
	$SQLupdate = "UPDATE detalle_mallas SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_dm";
	$resultado = pg_query($bdcon, $SQLupdate);
	if (!$resultado) {
		echo(msje_js(pg_last_error()));
	} else {
		$filas = pg_affected_rows($resultado);
	};
	if ($filas > 0) {
		echo(msje_js("Se han guardado los cambios en esta malla"));
		echo(js("location.href='$enlbase=editar_detalle_malla&id_malla=$id_malla';"));
		exit;
	};
};

if ($_REQUEST['pre_requisito'] <> "") {
	$id_dm_req = $_REQUEST['pre_requisito'];
	$SQLinsert = "INSERT INTO requisitos_malla VALUES ($id_dm,$id_dm_req,1);";
	$resultado = pg_query($bdcon, $SQLinsert);
	if (!$resultado) {
		echo(msje_js(pg_last_error()));
	};
};	
	
if ($_REQUEST['post_requisito'] <> "") {
	$id_dm_req = $_REQUEST['post_requisito'];
	$SQLinsert = "INSERT INTO requisitos_malla VALUES ($id_dm,$id_dm_req,2);";
	$resultado = pg_query($bdcon, $SQLinsert);
	if (!$resultado) {
		echo(msje_js(pg_last_error()));
	};
};	

if ($_REQUEST['borrar'] == "Borrar") {
	$id_dm_req = $_REQUEST['id_dm_req'];
	$tipo = $_REQUEST['tipo'];
	$SQLdelete = "DELETE FROM requisitos_malla WHERE id_dm=$id_dm AND id_dm_req=$id_dm_req AND tipo=$tipo;";
	$resultado = pg_query($bdcon, $SQLdelete);
	if (!$resultado) {
		echo(msje_js(pg_last_error()));
	};
};

$SQLtxt2    = "SELECT id,ano AS \"año\",carrera,niveles,id_escuela,id_carrera,cant_asig_oblig
               FROM vista_mallas WHERE id=$id_malla;";
$resultado2 = pg_query($bdcon, $SQLtxt2);
$filas2     = pg_numrows($resultado2);
if ($filas2 > 0) {
	$malla = pg_fetch_all($resultado2);
	$niveles         = $malla[0]['niveles'];
	$id_carrera      = $malla[0]['id_carrera'];
	$id_escuela      = $malla[0]['id_escuela'];

        $aNiveles = array();
	for($x=1;$x<=$niveles;$x++) {
		$aNiveles = array_merge($aNiveles,array(array('id'=>$x,'nombre'=>$x)));
	};
	
	$SQLtxt3 = "SELECT id,ano || '/' || asignatura AS nombre 
	            FROM vista_prog_asig 
	            WHERE id NOT IN (SELECT id_prog_asig 
	                             FROM detalle_mallas
	                             WHERE id_malla=$id_malla AND id<>$id_dm)
	                  AND id_carrera = $id_carrera OR id_carrera IS NULL
	            ORDER BY cod_asignatura;";

	$SQLtxt31 = "SELECT id,ano || '/' || asignatura AS nombre 
	             FROM vista_prog_asig 
	             WHERE id NOT IN (SELECT id_prog_asig 
	                              FROM detalle_mallas
	                              WHERE id_malla=$id_malla AND id<>$id_dm)
                      AND id_carrera <> $id_carrera
                ORDER BY cod_asignatura;";
                
	$resultado3  = pg_query($bdcon, $SQLtxt3);
	$resultado31 = pg_query($bdcon, $SQLtxt31);
	$filas3  = pg_numrows($resultado3);
	$filas31 = pg_numrows($resultado31);
	if ($filas3 > 0) {
		$prog_asig3 = pg_fetch_all($resultado3);
	};
	if ($filas31 > 0) {
		$prog_asig31 = pg_fetch_all($resultado31);
	};
	if ($filas3 == 0 && $filas31 == 0) {
		$mensaje  = "Al parecer no hay asignaturas con sus respectivos programas de ";
		$mensaje .= "estudios disponibles. Sí lo que desea es cambiar una asignatura ";
		$mensaje .= "ingresada erróneamente, elimínela y vuélvala a insertar.";
		echo(msje_js($mensaje));
	};
	$prog_asig = array_merge($prog_asig3,$prog_asig31);
	
	$SQLtxt4 = "SELECT id,nombre FROM lineas_tematicas WHERE id_escuela=$id_escuela;";
	$resultado4 = pg_query($bdcon, $SQLtxt4);
	$filas4 = pg_numrows($resultado4);
	if ($filas4 > 0) {
		$lineas_tematicas = pg_fetch_all($resultado4);
	};
	
	$SQLtxt5 = "SELECT id,nombre FROM caracter_asig;";
	$resultado5 = pg_query($bdcon, $SQLtxt5);
	$filas5 = pg_numrows($resultado5);
	if ($filas5 > 0) {
		$caracter_asig = pg_fetch_all($resultado5);
	};
	
	$SQLtxt6 = "SELECT * FROM vista_requisitos_malla WHERE id_dm=$id_dm ORDER BY cod_asignatura";
	$resultado6 = pg_query($bdcon, $SQLtxt6);
	$filas6 = pg_numrows($resultado6);
	if ($filas6 > 0) {
		$requisitos = pg_fetch_all($resultado6);
	};
	
	$SQLtxt7 = "SELECT id,cod_asignatura||' '||asignatura AS nombre
	            FROM vista_detalle_malla
	            WHERE id_malla=$id_malla AND nivel<$nivel AND 
	                  id NOT IN (SELECT id_dm_req 
	                             FROM vista_requisitos_malla
	                             WHERE id_dm=$id_dm)
	            ORDER BY cod_asignatura;";
	$resultado7 = pg_query($bdcon, $SQLtxt7);
	$filas7 = pg_numrows($resultado7);
	if ($filas7 > 0) {
		$detalle_mallas[1] = pg_fetch_all($resultado7);
	};
	
	$SQLtxt8 = "SELECT id,asignatura AS nombre
	            FROM vista_detalle_malla
	            WHERE id_malla=$id_malla AND nivel>$nivel AND
	                  id NOT IN (SELECT id_dm_req 
	                             FROM vista_requisitos_malla
	                             WHERE id_dm=$id_dm);";
	$resultado8 = pg_query($bdcon, $SQLtxt8);
	$filas8 = pg_numrows($resultado8);
	if ($filas8 > 0) {
		$detalle_mallas[2] = pg_fetch_all($resultado8);
	};	
};
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="get" onSubmit="return enblanco2('id_prog_asig','caracter');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_dm" value="<?php echo($id_dm); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($malla[0]['carrera']); ?> - <?php echo($malla[0]['año']); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla">
      <input type="submit" name="guardar" value="Guardar">
    </td>
    <td class="tituloTabla">
      <input type="submit" name="eliminar" value="Eliminar asignatura">
    </td>
    <td class="tituloTabla">
      <input type="button" name="cancelar" value="Cancelar"  onClick="window.location='<?php echo("$enlbase=editar_detalle_malla&id_malla=$id_malla"); ?>';">
    </td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr'>
      <select name='id_prog_asig' id='id_prog_asig' onChange="cambiado();">
        <?php echo(select($prog_asig,$detalle_malla[0]['id_prog_asig'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Caracter:</td>
    <td class='celdaValorAttr'>
      <select name='caracter' onChange="cambiado();">
        <?php echo(select($caracter_asig,$detalle_malla[0]['caracter'])); ?>
      </select>
    </td>
  </tr>

  <tr>
    <td class='celdaNombreAttr'>Ofertable:</td>
    <td class='celdaValorAttr'>
      <select name='ofertable' onChange="cambiado();">
        <?php echo(select($sino,$detalle_malla[0]['ofertable'])); ?>
      </select>
    </td>
  </tr>


  <tr>
    <td class='celdaNombreAttr'>Nivel:</td>
    <td class='celdaValorAttr'>
      <select name='nivel' onChange="cambiado();">
        <?php echo(select($aNiveles,$detalle_malla[0]['nivel'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>L&iacute;nea Tem&aacute;tica:</td>
    <td class='celdaValorAttr'>
      <select name='linea_tematica' onChange="cambiado();">
        <?php echo(select($lineas_tematicas,$detalle_malla[0]['linea_tematica'])); ?>
      </select>
    </td>
  </tr>
</table>
<br>
<table cellspacing="1" cellpadding="2" border="0">
  <tr valign="top">
	<?php
		for($tipo=1;$tipo<2;$tipo++) {				
	?>
    <td>
      <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
        <tr class='filaTituloTabla'>
          <td class='tituloTabla'><?php echo($requisitos_tipo[$tipo]['nombre']); ?></td>
        </tr>
		<?php
			if ($filas6 > 0) {
				for ($x=0; $x<$filas6; $x++) {
					if ($requisitos[$x]['tipo'] == $tipo) {
						$asignatura_req = $requisitos[$x]['cod_asignatura_req']." ".$requisitos[$x]['asignatura_req'];
						$enl = "$enlbase=$modulo&id_dm=$id_dm&borrar=Borrar&tipo=$tipo&id_dm_req=" . $requisitos[$x]['id_dm_req'];
						echo("  <tr class='filaTabla' onClick=\"return confirmar_borrar('$enl','" . $requisitos[$x]['asignatura_req'] . "');\">\n");
						echo("    <td class='textoTabla'>&nbsp;$asignatura_req</td>\n");
						echo("  </tr>\n");
					};
				};
			} else {
				echo("  <tr>\n");
				echo("    <td class='textoTabla'>No hay " . $requisitos_tipo[$tipo]['campo'] . " asignados para esta asignatura</td>\n");
				echo("  </tr>\n");
			};
		?>
        <tr>
          <td class='textoTabla'>
            <select name="<?php echo($requisitos_tipo[$tipo]['campo']); ?>" id="<?php echo($requisitos_tipo[$tipo]['campo']); ?>" onChange="submitform();">
              <option value=''>-- Seleccione --</option>
              <?php echo(select($detalle_mallas[$tipo],null)); ?>
            </select>
            <script>document.getElementById("pre_requisito").focus();</script>
          </td>
        </tr>
      </table>
    </td>
	<?php
		};
	?>
  </tr>
</table>
<div class="texto">Para añadir un requisito de asignatura, selecci&oacute;nela desde la lista</div>
<div class="texto">Pinche sobre el nombre de la asignatura para borrarla de la lista</div>
</form>

<!-- Fin: <?php echo($modulo); ?> -->

<script>
  $(document).ready(function () {
      $('#id_prog_asig').selectize({
          sortField: 'text'
      });
  });

</script>