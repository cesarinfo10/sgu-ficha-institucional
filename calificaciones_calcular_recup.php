<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_curso = $_REQUEST['id_curso'];
if ($id_curso == "") {
	echo(js("location.href='principal.php?modulo=calificaciones';"));
	exit;
};

$SQL_curso = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,
                     CASE WHEN vc.semestre=1 THEN 'Primero' ELSE 'Segundo' END AS semestre,vc.ano,
                     vc.profesor,vc.carrera,
                     coalesce(vc.sesion1,'')||' '||coalesce(vc.sesion2,'')||' '||coalesce(vc.sesion3,'') AS horario,
                     vc.id_prog_asig,count(ca.id_alumno) AS cant_alumnos
              FROM vista_cursos AS vc
              LEFT JOIN cargas_academicas AS ca ON ca.id_curso=vc.id 
              WHERE vc.id = '$id_curso'
              GROUP BY vc.id,vc.cod_asignatura,vc.seccion,vc.asignatura,vc.semestre,vc.ano,
                       vc.profesor,vc.carrera,vc.sesion1,vc.sesion2,vc.sesion3,vc.id_prog_asig;";
$curso = consulta_sql($SQL_curso);

if (count($curso) > 0) {
        $SQL_alumnos_curso = "SELECT id_alumno,rut,nombre_alumno,s1,nc,s2,recup,prom
                              FROM vista_cursos_alumnos
                              WHERE id_curso = '$id_curso' AND der_recup='Si';";
        $alumnos_curso = consulta_sql($SQL_alumnos_curso);

	$SQL_alumnos_curso2 = "SELECT id_alumno 
	                       FROM vista_cursos_alumnos
	                       WHERE id_curso = '$id_curso' AND s1 IS NOT NULL AND nc IS NOT NULL AND s2 IS NOT NULL;";
	$alumnos_curso2 = consulta_sql($SQL_alumnos_curso2);
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($curso[0]['asignatura']); ?>  
</div><br>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
<?php
	extract($curso[0]);
	
	$aCurso = array("Número de Acta"      => $id,
	                "Asignatura"          => $asignatura,
	                "Semestre"            => $semestre,
	                "Año"                 => $ano,
	                "Profesor"            => $profesor,
	                "Carrera"             => $carrera,
	                "Horario"             => $horario,
	                "Inscrito(a)s"        => $cant_alumnos." alumno(a)s");

	$HTML_enc_curso = tabla_encabezado($aCurso);
	echo($HTML_enc_curso);	
?>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="4">Alumnos con DERECHO a rendir Recuperativa</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>RUT</td>
    <td class='tituloTabla'>Nombre alumno(a)</td>
    <td class='tituloTabla'>Promedio</td>
  </tr>
<?php
	$HTML_alumnos_curso = "";
	for ($x=0; $x<count($alumnos_curso); $x++) {
		extract($alumnos_curso[$x]);
		$HTML_alumnos_curso .= "<tr class='filaTabla'>\n"
		                     . "  <td class='textoTabla' align='right'>$id_alumno</td>\n"
		                     . "  <td class='textoTabla' align='right'>$rut</td>\n"
		                     . "  <td class='textoTabla'>$nombre_alumno</td>\n"
		                     . "  <td class='textoTabla' align='center'>$prom</td>\n"
		                     . "</tr>\n";
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
