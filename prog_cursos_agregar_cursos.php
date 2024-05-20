<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

if (!is_numeric($_REQUEST['id_prog_curso'])) {
	echo(js("window.location='$enlbase=';"));
	exit;
} else {
	$id_prog_curso = $_REQUEST['id_prog_curso'];
}

if ($_REQUEST['guardar'] == "Guardar") {
	$aId_PA = $_REQUEST['id_pa'];
	$aSecs  = $_REQUEST['secs'];
	$aCursos = array();
	for($x=0;$x<count($aId_PA);$x++) {
		$id_pa = $aId_PA[$x];
		$aCursos = array_merge($aCursos,array(array("id_pa"=>$id_pa,"secs"=>$aSecs[$id_pa])));
	}
	$SQLinsert_pcd = "";
	for($x=0;$x<count($aCursos);$x++) {
		for($sec=1;$sec<=$aCursos[$x]['secs'];$sec++) {
			$SQLinsert_pcd .= "INSERT INTO prog_cursos_detalle (id_prog_curso,id_prog_asig,seccion)
			                        VALUES ($id_prog_curso,{$aCursos[$x]['id_pa']},$sec);";
		}
	}
	consulta_dml($SQLinsert_pcd);
	echo(js("window.location='$enlbase=prog_cursos_editar&id_prog_curso=$id_prog_curso';"));
	exit;
}

$prog_curso = consulta_sql("SELECT * FROM vista_prog_cursos WHERE id=$id_prog_curso");
if (count($prog_curso) == 0) {
	echo(js("window.location='$enlbase=';"));
	exit;
}

extract($prog_curso[0]);

$carreras = consulta_sql("SELECT id,nombre FROM carreras WHERE id_escuela=$id_escuela ORDER BY nombre");
if (is_numeric($_REQUEST['id_carrera'])) {
	$id_carrera=$_REQUEST['id_carrera'];
	$mallas = consulta_sql("SELECT id,ano AS nombre FROM mallas WHERE id_carrera=$id_carrera ORDER BY ano");
	$aNiveles = array(array("id" => "1","nombre" => "Impares"),
	                  array("id" => "2","nombre" => "Pares"));

//	if ($SEMESTRE == 1) { $_REQUEST['niveles']=2; } elseif ($SEMESTRE == 0) { $_REQUEST['niveles']=1; }
	
	if (is_numeric($_REQUEST['id_malla'])) {
		$id_malla = $_REQUEST['id_malla'];
		if ($_REQUEST['niveles'] == 1) {
			$condiciones = "AND nivel in (1,3,5,7,9,11)";
		} elseif ($_REQUEST['niveles'] == 2) {
			$condiciones = "AND nivel in (2,4,6,8,10,12)";
		}
		
		$SQL_asignaturas = "SELECT vdm.id_prog_asig,vdm.cod_asignatura,vdm.asignatura,vdm.nivel,pcd.id_prog_asig AS id_pa_pcd
		                    FROM vista_detalle_malla AS vdm
		                    LEFT JOIN (SELECT DISTINCT ON (id_prog_asig) id_prog_asig FROM prog_cursos_detalle WHERE id_prog_curso=$id_prog_curso) AS pcd ON pcd.id_prog_asig=vdm.id_prog_asig
		                    WHERE id_malla=$id_malla $condiciones ORDER BY nivel,cod_asignatura";
		$asignaturas = consulta_sql($SQL_asignaturas);
		if (count($asignaturas) > 0) {
			$HTML_asignaturas = "";
			for ($x=0;$x<count($asignaturas);$x++) {
				if ($nivel <> $asignaturas[$x]['nivel']) { 
					$nivel = $asignaturas[$x]['nivel'];					
					$HTML_asignaturas .= "<tr class='filaTabla'>"
					                  .  "  <td class='textoTabla' colspan='3' align='center'>"
					                  .  "    {$NIVELES[$nivel-1]['nombre']} nivel (o semestre)"
					                  .  "  </td>"
					                  .  "</tr>\n";
				}
		
				extract($asignaturas[$x]);

				$deshabilitado = "";
				if ($id_prog_asig == $id_pa_pcd) { $asignatura .= " *"; $deshabilitado = "disabled"; }
								
				$HTML_asignaturas .= "<tr class='filaTabla'>"
				                  .  "  <td class='textoTabla'><input type='checkbox' name='id_pa[]' id='id_pa[$id_prog_asig]' value='$id_prog_asig' $deshabilitado></td>"
				                  .  "  <td class='textoTabla'><label for='id_pa[$id_prog_asig]'>$cod_asignatura $asignatura</label></td>"
				                  .  "  <td class='textoTabla' align='center'><input type='text' style='text-align: center' name='secs[$id_prog_asig]' value='1' size='1' $deshabilitado></td>"
				                  .  "</tr>\n";
			}
		} else {
			$HTML_asignaturas = "<tr class='filaTabla'>"
			                  . "  <td class='textoTabla' colspan='3' align='center'>*** No hay asignaturas para esta malla ***</td>"
			                  . "</tr>";
		}
	}
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>

<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_prog_curso" value="<?php echo($id_prog_curso); ?>">

<?php if (count($asignaturas) > 0) { ?>
<table cellpadding="4" cellspacing="0" border="0" class="tabla">
  <tr>
    <td align="center" class="textoTabla" style="vertical-align: middle;">
      <input type="submit" name="guardar" value="Guardar">
      <input type="button" name="cancelar" value="Cancelar">
    </td>
  </tr>
</table><br>
<?php } ?>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">Escuela:</td>
    <td class="celdaValorAttr"><?php echo($escuela); ?></td>
    <td class="celdaNombreAttr">Periodo:</td>
    <td class="celdaValorAttr"><?php echo($periodo); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Creador:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($creador); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Fec. Creación:</td>
    <td class="celdaValorAttr"><?php echo($fecha); ?></td>
    <td class="celdaNombreAttr">Fec. Últ. Mod.:</td>
    <td class="celdaValorAttr"><?php echo($fecha_mod); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Cant. cursos:</td>
    <td class="celdaValorAttr"><?php echo($cant_cursos); ?></td>
    <td class="celdaNombreAttr">Cant. Profesores:</td>
    <td class="celdaValorAttr"><?php echo($cant_profes); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrera:</td>
    <td class="celdaValorAttr" colspan="3">
      <select name="id_carrera" onChange="submitform();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($carreras,$id_carrera)); ?>
      </select>
    </td>
  </tr>
<?php if (count($mallas) > 0) { ?>
  <tr>
    <td class="celdaNombreAttr">Niveles:</td>
    <td class="celdaValorAttr">
      <select name="niveles">
        <option value="">Todos</option>
        <?php echo(select($aNiveles,$_REQUEST['niveles'])); ?>
      </select>
    </td>
    <td class="celdaNombreAttr">Malla:</td>
    <td class="celdaValorAttr">
      <select name="id_malla" onChange="submitform();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($mallas,$id_malla)); ?>
      </select>
    </td>
  </tr>
<?php } ?>  
</table><br>

<?php if (count($asignaturas) > 0) { ?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>&nbsp;</td>
    <td class='tituloTabla'>Asignatura</td>
    <td class='tituloTabla'>Secciones</td>
  </tr>
  <?php echo($HTML_asignaturas); ?>
</table><br>
<div class='texto'>
  * Este curso ya se encuentra en la actual programación.
  Use la opción <a href='<?php echo("$enlbase=prog_cursos_agregar_curso&id_prog_curso=$id"); ?>' class='boton'>Agregar un curso</a>
  para agregar una sección más de un curso que ya tenga en su programación de cursos.
</div>
<?php } ?>

</form>

<!-- Fin: <?php echo($modulo); ?> -->
