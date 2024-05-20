<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_curso = $_REQUEST['id_curso'];
$calif    = $_REQUEST['calificacion'];
$token    = $_REQUEST['token'];
$volver   = $_REQUEST['volver'];

$botonCancelar = "location.href='".base64_decode($volver)."';";

if ($id_curso == "" || $calif == "" || $token == "") {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$SQL_tiempo_calificaciones = "SELECT * FROM tiempo_calificaciones
                              WHERE semestre=$SEMESTRE AND ano=$ANO AND $calif;";
$tiempo_calificaciones = consulta_sql($SQL_tiempo_calificaciones);

if (count($tiempo_calificaciones) == 0) {
	echo(msje_js("Esta calificación aún no está activa."));
	echo(js("location.href='principal.php?modulo=calificaciones';"));
	exit;
}

$nombre_calificacion = ucfirst($calif);

$SQL_curso = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.semestre,
                     vc.ano,vc.profesor,vc.id_profesor,vc.carrera,cant_alumnos_asist(vc.id),
                     coalesce(vc.sesion1,'')||' '||coalesce(vc.sesion2,'')||' '||coalesce(vc.sesion3,'') as horario,
                     vc.id_prog_asig,cantidad_alumnos(vc.id) AS cant_alumnos,c.cupo,c.cant_notas_parciales,token AS cod,
                     CASE WHEN c.cerrado THEN 'Cerrado' ELSE 'Abierto' END AS estado,coalesce(c.cupo,0) AS cupo,
                     coalesce(to_char(c.fec_ini,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_ini,coalesce(to_char(c.fec_fin,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_fin,
                     coalesce(to_char(c.fec_sol1,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol1,coalesce(to_char(c.fec_sol2,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol2,
                     coalesce(to_char(c.fec_sol_recup,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol_recup
              FROM vista_cursos AS vc
              LEFT JOIN cursos AS c USING(id)
              LEFT JOIN vista_cursos_cod_barras AS vccb USING(id)
              WHERE vc.id=$id_curso;";
$curso = consulta_sql($SQL_curso);

if (count($curso) > 0) {
	extract($curso[0]);

	if ($token <> md5($id_curso.$id_profesor)) { 
		echo(msje_js("Error de consistencia. No se puede continuar"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
	
	if ($estado == "Cerrado") {
		echo(msje_js("ERROR: Este curso se encuentra CERRADO y no es posible alterar las calificaciones. Contactese con su Escuela para resolver."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
	
	$SQL_cursos_fusion = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,c.id_prog_asig 
	                      FROM vista_cursos AS vc
	                      LEFT JOIN cursos AS c USING (id)
	                      WHERE id_fusion = $id_curso";
	$cursos_fusion     = consulta_sql($SQL_cursos_fusion);
	$HTML_fusionadas = "";
	$ids_cursos = $ids_pa = "";
	for ($x=0;$x<count($cursos_fusion);$x++) {
		$HTML_fusionadas .= "<small><br>&nbsp;<big><b>↳</b></big>{$cursos_fusion[$x]['asignatura']}</small>";
		$ids_cursos      .= "{$cursos_fusion[$x]['id']},";
		$ids_pa          .= "{$cursos_fusion[$x]['id_prog_asig']},";
	}
	
	$ids_cursos .= $id_curso;
	$ids_pa     .= $id_prog_asig;
	
	$SQL_alumnos_curso = "SELECT vca.id_ca,id_alumno,nombre_alumno,situacion,$calif
	                      FROM calificaciones_parciales AS cp
	                      LEFT JOIN vista_cursos_alumnos AS vca USING (id_ca)
	                      WHERE id_curso IN ($ids_cursos)
	                      ORDER BY nombre_alumno;";
	$alumnos_curso = consulta_sql($SQL_alumnos_curso);
	
	if (count($alumnos_curso) > 0) {
		$campos_validar = "";
		for ($x=0;$x<count($alumnos_curso);$x++) {
			$campos_validar .= "'id_ca[{$alumnos_curso[$x]['id_ca']}]',";
		}
		$campos_validar = substr($campos_validar,0,strlen($campos_validar)-1);
	}
	
	if (count($alumnos_curso) == 0) {
		echo(msje_js("Esta calificación ya fue ingresada.\\n"
		            ."Si necesita realizar alguna rectificación, acérquese a la Dirección "
		            ."de Escuela de la carrera que aparece en el Acta de curso"));
		echo(js("location.href='principal.php?modulo=calificaciones_ver_curso&id_curso=$id_curso';"));
	}
		
}

if ($_REQUEST['guardar'] == "Guardar" || $token == md5($id_curso.$id_profesor)) {

	$ids_ca = $_REQUEST['id_ca'];	
	$SQL_upd_ca = $SQL_upd_nc = "";
	foreach($ids_ca AS $id_ca => $nota) {
		if ($nota == "NSP") { $nota = -1; }
		$SQL_upd_ca .= "UPDATE calificaciones_parciales SET $calif = $nota WHERE id_ca = $id_ca;";
	}
	if (consulta_dml($SQL_upd_ca) > 0) {
		consulta_dml("UPDATE cargas_academicas SET nota_catedra = null,nota_final = null, id_estado=(CASE WHEN id_estado IN (6,10,11) THEN id_estado ELSE NULL END) WHERE id_curso IN (SELECT id FROM cursos WHERE $id_curso IN (id,id_fusion))");
		echo(msje_js("Se han guardado las calificaciones parciales"));
		echo(js("location.href='".base64_decode($volver)."';"));
	}

/*
	foreach($_REQUEST AS $nombre_campo => $valor_campo) {
		
		if (substr($nombre_campo,0,5) == "IDCA_") {

			$largo_id_ca_nota = strlen($nombre_campo);
			$id_ca_nota = substr($nombre_campo,5,$largo_id_ca_nota);
			
			$id_ca_valido = false;
			for($x=0;$x<count($alumnos_curso);$x++) {
				if ($alumnos_curso[$x]['id_ca'] == $id_ca_nota) {
					$id_ca_valido = true;
				}
			}
						
			if ($id_ca_valido) {
				if ($valor_campo == "NSP") { $valor_campo = -1; }
				// agregar a esta sentencia en el SET: promedio=null 
				$SQL_update_Calificaciones_parciales = "UPDATE calificaciones_parciales
				                                        SET $calif = $valor_campo
				                                        WHERE id_ca = '$id_ca_nota';";
				$SQL_update_cargas_academicas = "UPDATE cargas_academicas
				                                 SET nota_catedra=null,nota_final=null
				                                 WHERE id = '$id_ca_nota';";

				consulta_dml($SQL_update_Calificaciones_parciales);
				consulta_dml($SQL_update_cargas_academicas);
			}			
		}		
	}
	* */
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post" onSubmit="if (validar_nota(<?php echo($campos_validar); ?>) && confirm('Está seguro de informar estas notas')) { return true; } else { return false; }">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_curso" value="<?php echo($id_curso); ?>">
<input type="hidden" name="calificacion" value="<?php echo($calif); ?>">
<input type="hidden" name="token" value="<?php echo($token); ?>">
<input type="hidden" name="volver" value="<?php echo($volver); ?>">

<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
  <input type="button" value="Cancelar" onClick="<?php echo($botonCancelar); ?>">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'>Nº Acta:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($semestre."-".$ano); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($asignatura . " " . $prog_asig . $HTML_fusionadas); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($carrera); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Profesor:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($profesor); ?> <?php echo($ficha_prof); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Inscrito(a)s:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos); ?> alumno(a)s</td>
    <td class='celdaNombreAttr'>Asistentes:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos_asist); ?> alumno(a)s</td>
  </tr>
</table>
<div class="texto" style='margin-top: 5px'>
  Sr(a) Docente, recuerde que la calificación o nota debe ser ingresada a todos los alumnos listados de una vez (no pueden quedar casilleros en blanco) y estas tienen 2 formatos:
  <ul>
    <li>Una nota o número (p.e. 4.2 cuatro punto dos)</li>
    <li>NSP que se utiliza cuando un alumno(a) no se presentó a rendir su prueba (Rogamos encarecidamente no calificar con nota 1.0 en estos casos).</li>
  </ul>
  <b>Dispone de 15 minutos</b> para registrar estas calificaciones, de otra forma el SGU cerrará la sesión con la consiguiente pérdida de información que haya alcanzado a ingresar.
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Nombre alumno(a)</td>
    <td class='tituloTabla'><?php echo($nombre_calificacion); ?></td>
  </tr>
<?php
	$HTML_alumnos_curso = "";
	for ($x=0; $x<count($alumnos_curso); $x++) {
		extract($alumnos_curso[$x]);

		$cp = $alumnos_curso[$x][$calif];
		
		$disabled = "";
		$situacion = trim($situacion);
		if ($situacion == "Suspendido" || $situacion == "Abandono" || $situacion == "Retirado") {
				$disabled = "readonly";
				$cp = "NSP";
		}
				
		//$deshabilitado = "readonly";
		$deshabilitado = "";
		if (is_null($cp) || ($cp <> "" && $_SESSION['tipo'] <= 1)) {
			$deshabilitado = "";
		}
		
		if ($cp == -1) { $cp = "NSP"; }
		
		$HTML_alumnos_curso .= "<tr class='filaTabla'>\n"
		                     . "  <td class='textoTabla' align='right'>$id_alumno</td>\n"
		                     . "  <td class='textoTabla'>$nombre_alumno</td>\n"
		                     . "  <td class='textoTabla' align='center'>"
		                     . "    <input type='text' class='boton' name='id_ca[$id_ca]' size='3' maxlength='3' "
		                     . "           style='font-weight: bold; text-align: center' "
		                     . "           value='$cp' "
		                     . "           onChange='this.value = this.value.toUpperCase();'"
		                     . "           onBlur=\"if(this.value==''){this.value='NSP';}\" $deshabilitado $disabled><br>$situacion"
		                     . "  </td>\n"
		                     . "</tr>\n";
	}
	echo($HTML_alumnos_curso);
?>
  <tr class='filaTabla'>
    <td class='textoTabla' colspan='2'>&nbsp;</td>
    <td class='celdaValorAttr' style='text-align: center'><input type="submit" name="guardar" value="Guardar"></td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

