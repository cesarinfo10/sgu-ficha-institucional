<?php 
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_examen = $_REQUEST['id_examen'];

if ($_REQUEST['id_etd_elim'] > 0 && $_REQUEST['id_docente_elim'] > 0) {
	consulta_dml("DELETE FROM examenes_terminales_docentes WHERE id={$_REQUEST['id_etd_elim']} AND id_examen=$id_examen AND id_profesor={$_REQUEST['id_docente_elim']}");
}

$ID_GLOSA_EXAMEN = "10,47,5,9"; // glosa examen de grado

$SQL_exam_term = "SELECT et.id,to_char(fecha_reg,'dd-tmMon-YYYY HH24:MI') AS fecha_reg,
                         estado,to_char(estado_fecha,'dd-tmMon-YYYY') AS estado_fecha,
                         CASE WHEN virtual THEN 'Si' ELSE 'No' END AS virtual,
                         et.tipo,tema,to_char(fecha_examen,'tmDay dd-tmMon-YYYY HH24:MI') AS fecha_examen,s.nombre AS sala,
						 u.nombre AS ministro_de_fe,ministro_de_fe_modalidad,e.nombre AS escuela,et.observaciones
                  FROM examenes_terminales AS et
				  LEFT JOIN vista_usuarios AS u ON u.id=et.id_ministro_de_fe
				  LEFT JOIN escuelas AS e ON e.id=et.id_escuela
				  LEFT JOIN salas AS s ON s.codigo=et.sala
                  WHERE et.id=$id_examen";

$exam_term = consulta_sql($SQL_exam_term);
if (count($exam_term) > 0) {

	$SQL_pago_examen = "SELECT 1 FROM finanzas.cobros WHERE id_alumno=a2.id AND id_glosa IN ($ID_GLOSA_EXAMEN) AND pagado AND fecha_venc>=now()::date-'6 months'::interval";

	$SQL_grupo_estud = "SELECT va2.id,va2.nombre,($SQL_pago_examen) AS pagado
						FROM examenes_terminales_estudiantes AS ete 
						LEFT JOIN vista_alumnos AS va2 ON va2.id=ete.id_alumno 
						LEFT JOIN alumnos AS a2 ON a2.id=va2.id
						LEFT JOIN carreras AS c2 ON c2.id=a2.carrera_actual
						WHERE ete.id_exam_term=$id_examen";
	$estudiantes = consulta_sql($SQL_grupo_estud);

	$HTML_est = "";
	$cant_pagado = 0;
	for ($x=0;$x<count($estudiantes);$x++) {
		$pagado = "⛔";
		$estudiante = "<a href='$enlbase_sm=ver_alumno&id_alumno={$estudiantes[$x]['id']}' class='enlaces' id='sgu_fancybox'>{$estudiantes[$x]['nombre']}</a>";
		if ($estudiantes[$x]['pagado'] == 1) {
			$cant_pagado++;
			$pagado = "✅";
		}
		$HTML_est .= "<li>$estudiante $pagado</li>";
	}

	$SQL_docentes = "SELECT etd.id,vp.nombre,etd.funcion,etd.area,etd.modalidad,etd.id_profesor
	                 FROM examenes_terminales_docentes AS etd
					 LEFT JOIN vista_profesores AS vp ON vp.id=id_profesor 
					 WHERE id_examen=$id_examen
           ORDER BY etd.id";
	$docentes = consulta_sql($SQL_docentes);

	$HTML = "";
	for ($x=0;$x<count($docentes);$x++) {

		$enl_elim = "$enlbase_sm=$modulo&id_examen=$id_examen&id_etd_elim={$docentes[$x]['id']}&id_docente_elim={$docentes[$x]['id_profesor']}";
		$elim = "<a class='enlaces' href='#' onClick=\"if (confirm('Desea eliminar al docente ({$docentes[$x]['nombre']}) del examen?')) { location.href='$enl_elim'; } \"><big style='color: red'>✗</big></a>";

		if (count($docentes)==1) { $elim = ""; }

		$HTML .= "<tr class='filaTabla'>"
		      .  "  <td class='textoTabla'>$elim {$docentes[$x]['nombre']}</td>"
		      .  "  <td class='textoTabla'>{$docentes[$x]['funcion']}</td>"
		      .  "  <td class='textoTabla'>{$docentes[$x]['area']}</td>"
		      .  "  <td class='textoTabla'>{$docentes[$x]['modalidad']}</td>"
		      .  "</tr>";
	}
	$HTML_docentes = $HTML;
}

$boton_acta = "";
if ($cant_pagado <> count($estudiantes)) {
	$msje = "ATENCIÓN: El acta no se puede emitir. \\n\\n"
	      . "No se encuentran pagados los Derechos de Titulación de uno o más estudiantes. \\n\\n"
		  . "Por favor, remitir los estudiantes marcados con ⛔ a Tesorería para regularizar esta situación.";
	$boton_acta = "<input type='button' onClick=\"alert('$msje');\" value='📜 Acta de Examen'>";
} else {
	$boton_acta = "<a href='examenes_terminales_acta.php?id_examen=$id_examen' class='boton'>📜 Acta de Examen</a>";
}

$boton_reg_notas = "";
if ($exam_term[0]['estado'] == "Cursado") {
	$boton_reg_notas = "<a href='$enlbase_sm=examenes_terminales_reg_calif&id=$id_examen' class='boton'>📋 Registrar Calificaciones</a>";
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<div class="texto" style='margin-top: 5px'>
  <a href='<?php echo("$enlbase_sm=examenes_terminales_editar&id_examen=$id_examen"); ?>' class='boton'>📝 Editar</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <?php echo($boton_acta); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <?php echo($boton_reg_notas); ?>
</div>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Examen</td></tr>
  <tr>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($exam_term[0]['id']); ?></td>
    <td class='celdaNombreAttr'>Estado:</td>
    <td class='celdaValorAttr'><?php echo($exam_term[0]['estado'] . " <i>desde el " . $exam_term[0]['estado_fecha'] . "</i>"); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tema:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($exam_term[0]['tema']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tipo:</td>
    <td class='celdaValorAttr'><?php echo($exam_term[0]['tipo']); ?></td>
    <td class='celdaNombreAttr'>Virtual:</td>
    <td class='celdaValorAttr'><?php echo($exam_term[0]['virtual']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha y hora:</td>
    <td class='celdaValorAttr'><?php echo($exam_term[0]['fecha_examen']); ?> sala <?php echo($exam_term[0]['sala']); ?></td>
    <td class='celdaNombreAttr'>Escuela:</td>
    <td class='celdaValorAttr'><?php echo($exam_term[0]['escuela']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Estudiante(s):</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($HTML_est); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Comisión</td></tr>

  <tr>
    <td class='celdaNombreAttr'><label for="id_ministro_de_fe">Ministro de Fé:</label></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($exam_term[0]['ministro_de_fe']); ?> en modalidad <?php echo($exam_term[0]['ministro_de_fe_modalidad']); ?></td>
  </tr>

  <tr>    
    <td class='celdaValorAttr' colspan="4">
      <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" width='100%' class="tabla">
	    <tr class='filaTituloTabla'>
		  <td class='tituloTabla'>Docente</td>
		  <td class='tituloTabla'>Función</td>
		  <td class='tituloTabla'>Área</td>
		  <td class='tituloTabla'>Modalidad</td>
		</tr>
        <?php echo($HTML_docentes); ?>
      </table>
	</td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Observaciones</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan="4"><?php echo($exam_term[0]['observaciones']); ?></td>
  </tr>
</table>
</form>

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1200,
		'height'			: 600,
		'afterClose'		: function () {  },
		'type'				: 'iframe'
	});
});
</script>