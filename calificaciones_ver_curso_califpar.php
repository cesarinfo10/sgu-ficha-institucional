<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

//$cant_max_cp = 7;

$id_curso = $_REQUEST['id_curso'];
$token    = $_REQUEST['token'];

if ($id_curso == "" || $token == "") {
	echo(js("location.href='principal.php?modulo=calificaciones';"));
	exit;
}

$volver = base64_encode($_SERVER['REQUEST_URI']);

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

	if ($curso[0]['cant_notas_parciales'] == "") {
		echo(msje_js("Actualmente no se encuentra definido el número de calificaciones parciales "
		            ."que aplicará para este curso en este semestre. Pinche en el botón Aceptar, "
		            ."para que SGU le permita definir este parámetro."));
		echo(js("location.href='$enlbase_sm=calificaciones_def_cant_notas_parciales&id_curso=$id_curso&token=$token&volver=$volver';"));
		exit;
	}

	if ($token <> md5($id_curso.$id_profesor)) { 
		echo(msje_js("Error de consistencia. No se puede continuar"));
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

	$SQL_alumnos_curso = "SELECT id_ca,id_alumno,va.rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre_alumno,
	                             va.carrera||'-'||a.jornada AS carrera,
	                             va.cohorte,va.semestre_cohorte,va.estado,c1,c2,c3,c4,c5,c6,c7,nc,
	                             recup,nf,situacion
	                      FROM vista_cursos_alumnos          AS vca
	                      LEFT JOIN vista_alumnos            AS va ON va.id=id_alumno
	                      LEFT JOIN alumnos                  AS a ON a.id=id_alumno
	                      LEFT JOIN calificaciones_parciales AS cp USING (id_ca)
	                      WHERE id_curso IN ($ids_cursos)
	                      ORDER BY nombre_alumno";
	                      
	/*$SQL_alumnos_curso2 = "SELECT id_alumno 
	                       FROM vista_cursos_alumnos
	                       WHERE id_curso = '$id_curso' AND c1 IS NOT NULL AND c2 IS NOT NULL AND s2 IS NOT NULL;";
	$alumnos_curso2 = consulta_sql($SQL_alumnos_curso2);*/
	
	$SQL_id_al_curso = "SELECT id_ca FROM ($SQL_alumnos_curso) AS al_curso";
	$SQL_incluir_al_califpar = "INSERT INTO calificaciones_parciales (id_ca)
	                            SELECT id FROM (SELECT id FROM cargas_academicas
	                                            WHERE id_curso IN ($ids_cursos)
	                                           EXCEPT
	                                            SELECT id_ca FROM calificaciones_parciales
	                                            WHERE id_ca in ($SQL_id_al_curso)) AS insertar";
	$incluir_al_califpar = consulta_dml($SQL_incluir_al_califpar);
	$alumnos_curso = consulta_sql($SQL_alumnos_curso);
	
};

$SQL_tiempo_calificaciones = "SELECT c1,c2,c3,c4,c5,c6,c7 FROM tiempo_calificaciones
                              WHERE semestre=$SEMESTRE AND ano=$ANO;";
$tiempo_calificaciones = consulta_sql($SQL_tiempo_calificaciones);

if (count($tiempo_calificaciones) > 0) {
	$botones = "";
	$HTML = "";
	$cant_cp = 0;
	
	for ($x=1;$x<=$curso[0]['cant_notas_parciales'];$x++) {
		$cp = "c".$x;
		if ($tiempo_calificaciones[0][$cp] == "t") {
			$enlcp   = "$enlbase_sm=calificaciones_ingresar_califpar&id_curso=$id_curso&calificacion=c$x&token=$token&volver=$volver";
			$botoncp = "<a href='$enlcp' class='boton' style='text-align: center'><small>Nota<br></small>C$x</a> ";
			$botones .= $botoncp;
			$HTML .= "<td class='tituloTabla'>C$x</td>\n";
			$cant_cp++;
		}
	}
	
	$enlCalcProm   = "$enlbase_sm=calificaciones_calcular_nc&id_curso=$id_curso&token=$token&volver=$volver";
	$botonCalcProm = "<a href='$enlCalcProm' class='boton' style='text-align: center'>Calcular Nota de Cátedra<br><small>(Promedio de Calificaciones Parciales)</small></a>";
}

$botonCantNP = "<a href='$enlbase_sm=calificaciones_def_cant_notas_parciales&id_curso=$id_curso&token=$token&volver=$volver' class='boton'>Cambiar Cantidad de Notas Parciales ($cant_notas_parciales)</a>";


if ($botones == "") { $botones = "Nada que calificar aún"; }
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<table cellpadding="4" cellspacing="0" border="0" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class="celdaFiltro" style="vertical-align: top;">
      Acciones:<br>
      <?php echo($botonCantNP); ?>
      <a href='#' onClick="parent.jQuery.fancybox.close();" class='boton'>Cerrar</a>
    </td>
  </tr>
</table>
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
<table cellpadding="4" cellspacing="0" border="0" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class="celdaFiltro" style="vertical-align: middle;" >
      Calificar:<br>
      <?php echo($botones); ?>
      <?php echo($botonCalcProm); ?>
    </td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="2">Alumnos</td>
    <td class='tituloTabla' colspan="<?php echo($cant_cp+1); ?>">Calificaciones Parciales</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Nombre alumno(a)</td>
    <?php echo($HTML); ?>
    <td class='tituloTabla'>Promedio</td>
  </tr>
<?php
	$HTML_alumnos_curso = "";
	for ($x=0; $x<count($alumnos_curso); $x++) {
		extract($alumnos_curso[$x]);

		$HTML_alumnos_curso .= "<tr class='filaTabla'>\n"
		                    .  "  <td class='textoTabla' align='right'>$id_alumno</td>\n"
		                    .  "  <td class='textoTabla'>$nombre_alumno</td>\n";

		for ($y=1;$y<=$curso[0]['cant_notas_parciales'];$y++) {
			$cp="c".$y;
			if ($tiempo_calificaciones[0][$cp] == "t") {
				$cp = $alumnos_curso[$x][$cp];								
				if ($cp == -1) { $cp = "NSP"; }
				$HTML_alumnos_curso .= "<td class='textoTabla' align='center'>$cp</td>\n";
			}
		} 
		$HTML_alumnos_curso .= "  <td class='textoTabla' align='center'>$nc</td>\n"
		                    .  "</tr>\n";



	}
	echo($HTML_alumnos_curso);
?>
</table>

<!-- Fin: <?php echo($modulo); ?> -->

<?php

function nombre_dia($numero_dia_semana) {
	$dias = array(1 => "Lun","Mar","Mie","Jue","Vie","Sab","Dom");
	return $dias[$numero_dia_semana];
};
?>
