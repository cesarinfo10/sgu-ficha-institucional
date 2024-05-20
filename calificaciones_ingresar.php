<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_curso     = $_REQUEST['id_curso'];
$token        = $_REQUEST['token'];
$calificacion = $_REQUEST['calificacion'];
if ($id_curso == "" || $calificacion == "" || $token == "") {
	echo(js("location.href='principal.php?modulo=calificaciones';"));
	exit;
}

$calif = "";
$fecha_calif = "";
switch ($calificacion) {
	case "solemne1":
		$calif = "s1"; $calif_nombre = "Solemne I";
		$fecha_calif = "fecha_solemne1";
		break;
	case "nota_catedra":
		$calif = "nc"; $calif_nombre = "Nota de Cátedra";
		$fecha_calif = "fecha_catedra";
		break;
	case "solemne2":
		$calif = "s2"; $calif_nombre = "Solemne II";
		$fecha_calif = "fecha_solemne2";
		break;
	case "recuperativa":
		$calif = "recup"; $calif_nombre = "Solemne Recuperativa";
		$fecha_calif = "fecha_recuperativa";
		break;
	default:
		echo(js("location.href='principal.php?modulo=calificaciones';"));
		exit;
}

$SQL_tiempo_calificaciones = "SELECT * FROM tiempo_calificaciones
                              WHERE semestre=$SEMESTRE AND ano=$ANO AND $calificacion;";
$tiempo_calificaciones = consulta_sql($SQL_tiempo_calificaciones);

if (count($tiempo_calificaciones) == 0) {
	echo(msje_js("Esta calificación aún no está activa."));
	echo(js("location.href='principal.php?modulo=calificaciones';"));
	exit;
}

$nombre_calificacion = ucfirst($calificacion);

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

//if ($_SESSION['id_usuario'] <> $curso[0]['id_profesor']) {
//	echo(msje_js("Que intentas malandrín."));
//	
//echo(js("location.href='principal.php?modulo=calificaciones';"));
//	exit;
//}

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
	
	$SQL_mallas = "SELECT char_comma_sum(alias_carrera||ano::text) AS anos FROM vista_mallas WHERE id IN (SELECT id_malla FROM detalle_mallas WHERE id_prog_asig IN ($ids_pa))";
	$mallas = consulta_sql($SQL_mallas);
	$mallas = $mallas[0]['anos'];	
/*	                      	
	$SQL_alumnos_curso = "SELECT id_alumno,nombre_alumno,situacion,$calif
	                      FROM vista_cursos_alumnos
	                      WHERE id_curso IN ($ids_cursos)
	                      ORDER BY fecha_mod,nombre_alumno;"; */
/* Se anula control de derecho a recuperativa desde 2011
 	if ($calif == "recuperativa") {
		$SQL_alumnos_curso = "SELECT id_alumno,rut,nombre_alumno
		                      FROM vista_cursos_alumnos
		                      WHERE id_curso = '$id_curso' AND der_recup='Si';";
	}
*/
	$SQL_alumnos_curso = "SELECT id_ca,id_alumno,va.rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre_alumno,
	                             va.carrera||'-'||a.jornada AS carrera,
	                             va.cohorte,va.semestre_cohorte,va.estado,s1,nc,s2,prom,der_recup,
	                             recup,nf,situacion
	                      FROM vista_cursos_alumnos
	                      LEFT JOIN vista_alumnos AS va ON va.id=id_alumno
	                      LEFT JOIN alumnos AS a ON a.id=id_alumno
	                      WHERE id_curso IN ($ids_cursos)
	                      ORDER BY nombre_alumno;";
	$alumnos_curso = consulta_sql($SQL_alumnos_curso);
	
	if (count($alumnos_curso) > 0) {
		$campos_validar = "";
		for ($x=0;$x<count($alumnos_curso);$x++) {
			$campos_validar[$x] = "'id_ca[{$alumnos_curso[$x]['id_ca']}]'";
		}
		$campos_validar = implode(",",$campos_validar);
	}
	
	if (count($alumnos_curso) == 0) {
		echo(msje_js("Esta calificación ya fue ingresada.\\n"
		            ."Si necesita realizar alguna rectificación, acérquese a la Dirección "
		            ."de Escuela de la carrera que aparece en el Acta de curso"));
		echo(js("location.href='principal.php?modulo=calificaciones_ver_curso&id_curso=$id_curso';"));
	}
		
}

if ($_REQUEST['guardar'] == "Guardar") {
	
	$ids_ca = $_REQUEST['id_ca'];	
	$SQL_upd_ca = "";
	foreach($ids_ca AS $id_ca => $nota) {
		if ($nota == "NSP") { $nota = -1; }
		$SQL_upd_ca .= "UPDATE cargas_academicas 
		                SET $calificacion = $nota, 
							nota_final = null, 
							id_estado=(CASE WHEN id_estado IN (6,10,11) THEN id_estado ELSE NULL END),
							$fecha_calif = now()
		                WHERE id = $id_ca;";
	}
	consulta_dml($SQL_upd_ca);
	 	
	/*
	foreach($_REQUEST AS $nombre_campo => $valor_campo) {		
		if (substr($nombre_campo,0,3) == "ID_") {
			$largo_id_alumno_nota = strlen($nombre_campo);
			$id_alumno_nota = substr($nombre_campo,3,$largo_id_alumno_nota);
			$id_alumno_valido = false;
			for($x=0;$x<count($alumnos_curso);$x++) {
				if ($alumnos_curso[$x]['id_alumno'] == $id_alumno_nota) {
					$id_alumno_valido = true;
				}
			}			
			if ($id_alumno_valido) {
				if ($valor_campo == "NSP") { $valor_campo = -1; }
				$SQL_update_Cargas_Academicas = "UPDATE cargas_academicas
				                                 SET $calificacion = $valor_campo
				                                 WHERE id_curso  = '$id_curso' AND
				                                       id_alumno = '$id_alumno_nota';";
				consulta_dml($SQL_update_Cargas_Academicas);
			}			
		}		
	}
	*/
	echo(msje_js("Se han guardado las calificaciones"));
	if (strpos($_SERVER['SCRIPT_NAME'],"_sm") === false) {
		echo(js("location.href='principal.php?modulo=calificaciones_ver_curso&id_curso=$id_curso';"));
	} else {
		echo(js("parent.jQuery.fancybox.close();"));
	}
}			
?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo . " $calif_nombre"); ?>
</div>
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post"
      onSubmit="if (validar_nota(<?php echo($campos_validar); ?>) && confirm('¿Está seguro de informar estas notas como oficiales (si pincha en «Aceptar», además se notificará a los estudiantes de este curso mediante correo electrónico) ?')) { return true; } else { return false; }">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_curso" value="<?php echo($id_curso); ?>">
<input type="hidden" name="token" value="<?php echo($token); ?>">
<input type="hidden" name="calificacion" value="<?php echo($calificacion); ?>">

<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
  <input type="button" value="Cancelar" onClick="parent.jQuery.fancybox.close();">
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
    <td class='tituloTabla'><?php echo($calif_nombre); ?></td>
  </tr>
<?php
	$HTML_alumnos_curso = "";
	for ($x=0; $x<count($alumnos_curso); $x++) {
		extract($alumnos_curso[$x]);
		
		$valor = $$calif;
		
		$disabled = "";
		if ($situacion == "Suspendido" || $situacion == "Abandono" || $situacion == "Retirado") {
			$disabled = "readonly";
			$valor    = "NSP";
		}
				 
		$HTML_alumnos_curso .= "<tr class='filaTabla'>\n"
		                     . "  <td class='textoTabla' align='right'>$id_alumno</td>\n"
		                     . "  <td class='textoTabla'>$nombre_alumno</td>\n"
		                     . "  <td class='textoTabla' align='center'>"
		                     . "    <input type='text' class='boton' name='id_ca[$id_ca]' value='$valor' size='3' maxlength='3' "
		                     . "           style='font-weight: bold; text-align: center' "
		                     . "           onChange='this.value = this.value.toUpperCase();'"
		                     . "           onBlur=\"if(this.value==''){this.value='NSP';}\" $disabled><br>$situacion"
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
