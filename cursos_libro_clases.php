<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_curso = $_REQUEST['id_curso'];

if ($id_curso == "") {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}


$SQL_curso = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.semestre,
                     vc.ano,vc.profesor,vc.id_profesor,vc.carrera,cant_alumnos_asist(vc.id),
                     coalesce(vc.sesion1,'')||' '||coalesce(vc.sesion2,'')||' '||coalesce(vc.sesion3,'') as horario,
                     c.dia1,c.dia2,c.dia3,
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

	if ($estado == "Cerrado") {
		echo(msje_js("AVISO: Este curso se encuentra CERRADO y no es posible alterar el contenido. Contáctese con su Escuela para resolver."));
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
    
	$SQL_alumnos_curso = "SELECT id_ca,id_alumno,nombre_alumno,situacion
	                      FROM vista_cursos_alumnos
	                      WHERE id_curso IN ($ids_cursos)
	                      ORDER BY nombre_alumno;";
	$alumnos_curso = consulta_sql($SQL_alumnos_curso);
	
	if (count($alumnos_curso) > 0) {
		$SQL_asist_total = "SELECT count(id_sesion) FROM ca_asistencia caa LEFT JOIN cargas_academicas ca ON ca.id=caa.id_ca WHERE id_sesion=cs.id AND (id_estado IS NULL OR id_estado NOT IN (6,10,11,12))";
		$SQL_presentes   = $SQL_asist_total." AND presente ";
		
        $SQL_sesiones = "SELECT id,to_char(fecha,'DD/MM') AS fecha,materia,metodologias,fecha_reg,
                                CASE WHEN ($SQL_asist_total)>0 THEN round(($SQL_presentes)::numeric*100/($SQL_asist_total)) ELSE 0 END AS tasa_presentes
                         FROM cursos_sesiones AS cs
                         WHERE id_curso IN ($ids_cursos)
                         ORDER BY cs.fecha";
        $sesiones = consulta_sql($SQL_sesiones);
        //echo($SQL_sesiones);
        if (count($sesiones) == 0) {
            $msje_confirm = "Actualmente no existen sesiones o clases definidas. Desea añadir la primera?";
            $confirma_si = "$enlbase_sm=cursos_libro_clases_nueva_sesion&id_curso=$id_curso";
            $confirma_no = "#";
            echo(confirma_js($msje_confirm,$confirma_si,$confirma_no));
            $HTML_sesiones = "<td class='tituloTabla'>"
                           . "  <a href='$enlbase_sm=cursos_libro_clases_nueva_sesion&id_curso=$id_curso' class='boton'>Nueva Sesión'</a>  "
                           . "</td>";
        } else {
            $HTML_sesiones = $HTML_tasa_presentes = "";
            $sum_tasas = 0;
            for ($x=0;$x<count($sesiones);$x++) { 
                $id_sesion = $sesiones[$x]['id'];
                $HTML_sesiones .= "<td class='tituloTabla'>"
                               .  "  <a href='$enlbase_sm=cursos_libro_clases_tomar_asistencia&id_curso=$id_curso&id_sesion=$id_sesion' class='boton'>🖋<small>{$sesiones[$x]['fecha']}</small></a> "
                               .  "</td>";
                $HTML_tasa_presentes .= "<td class='celdaNombreAttr' style='text-align: center; font-weight: light'>"
                                     .  "  {$sesiones[$x]['tasa_presentes']}% "
                                     .  "</td>";
				$sum_tasas += $sesiones[$x]['tasa_presentes'];
            }
            $prom_tasas = round($sum_tasas/count($sesiones),0);
        }

        $SQL_ca_asistencia = "SELECT id_sesion,cs.fecha_reg,caa.id_ca,presente,mod_fecha,mod_id_usuario
                              FROM ca_asistencia AS caa
                              LEFT JOIN vista_cursos_alumnos AS vca USING (id_ca)
                              LEFT JOIN cursos_sesiones AS cs ON cs.id=caa.id_sesion
                              WHERE vca.id_curso IN ($ids_cursos)
                              ORDER BY nombre_alumno,cs.fecha";
        $ca_asistencia = consulta_sql($SQL_ca_asistencia);

        $SQL_alumnos_provisorios = "SELECT vcat.id AS id_ca,vcat.rut,vcat.nombre AS nombre_alumno,vcat.id_alumno
                                    FROM vista_ca_temporal AS vcat
                                    WHERE id_curso IN ($ids_cursos)
                                    ORDER BY nombre_alumno;";
        $alumnos_provisorios = consulta_sql($SQL_alumnos_provisorios);

        $SQL_ca_temp_asist = "SELECT id_sesion,cs.fecha_reg,caa.id_ca_temporal,presente,mod_fecha,mod_id_usuario
                              FROM ca_temp_asist AS caa
                              LEFT JOIN vista_ca_temporal AS vca ON vca.id=caa.id_ca_temporal
                              LEFT JOIN cursos_sesiones AS cs ON cs.id=caa.id_sesion
                              WHERE vca.id_curso IN ($ids_cursos)
                              ORDER BY nombre,cs.fecha";
        $ca_temp_asist = consulta_sql($SQL_ca_temp_asist);

        $HTML_alumnos_curso = "";
        $j = 0;
        for($x=0;$x<count($alumnos_curso);$x++) {
			$estilo_alumno = "";
			$desertor = false;
			switch ($alumnos_curso[$x]['situacion']) {
				case "Suspendido":
				case "Abandono":
				case "Abandono T.":
					$estilo_alumno = "color: #4D4D4D; text-decoration: line-through";
					$desertor = true;
			}
            $HTML_alumnos_curso .= "<tr class='filaTabla'>\n"
                                .  "  <td class='textoTabla' align='right'><span style='$estilo_alumno'>{$alumnos_curso[$x]['id_alumno']}</span></td>"
                                .  "  <td class='textoTabla'><span style='$estilo_alumno'>{$alumnos_curso[$x]['nombre_alumno']}</span></td>\n";
            for ($y=0;$y<count($sesiones);$y++) {
                if ($sesiones[$y]['id'] == $ca_asistencia[$j]['id_sesion'] && $alumnos_curso[$x]['id_ca'] == $ca_asistencia[$j]['id_ca']) {
                    if ($ca_asistencia[$j]['presente'] == "t") { $presente = "<span style='color: green; $estilo_alumno'><b> ✔ </b></span>"; }
                    elseif ($ca_asistencia[$j]['presente'] == "f") { $presente = "<span style='color: red; $estilo_alumno'><b> ✘ </b></span>"; }
                    else { $presente = "<div style='background:#FFFF00'> ❓ </div>"; }
                    $HTML_alumnos_curso .= "  <td class='textoTabla' align='center'>$presente</td>\n";
                    $j++;
                } else {
                    $HTML_alumnos_curso .= "<td class='textoTabla' align='center'><div style='background:#FFFF00'> ❓ </div></td>\n";
                }
            }
            $HTML_alumnos_curso .= "</tr>\n";            
        }

        $HTML_alumnos_prov = "";
        $j = 0;
        $ids_alumnos = array_column($alumnos_curso,'id_alumno');
        for($x=0;$x<count($alumnos_provisorios);$x++) {
			$id_alumno = $alumnos_provisorios[$x]['id_alumno'];
			if (array_search($id_alumno,$ids_alumnos) > 0) { 
				$id_alumno = "<a href='$enlbase_sm=$modulo&id_curso=$id_curso&traspasar_id_alumno=$id_alumno' class='enlaces'>🔼</a>";
			}
            $HTML_alumnos_prov  .= "<tr class='filaTabla'>\n"
                                .  "  <td class='textoTabla' align='right'>$id_alumno</td>"
                                .  "  <td class='textoTabla'>{$alumnos_provisorios[$x]['nombre_alumno']}</td>\n";
            for ($y=0;$y<count($sesiones);$y++) {
                if ($sesiones[$y]['id'] == $ca_temp_asist[$j]['id_sesion'] && $alumnos_provisorios[$x]['id_ca'] == $ca_temp_asist[$j]['id_ca_temporal']) {
                    if ($ca_temp_asist[$j]['presente'] == "t") { $presente = "<span style='color: green'><b> ✔ </b></span>"; }
                    elseif ($ca_temp_asist[$j]['presente'] == "f") { $presente = "<span style='color: red'><b> ✘ </b></span>"; }
                    else { $presente = "<div style='background:#FFFF00'> ❓ </div>"; }
                    $HTML_alumnos_prov .= "  <td class='textoTabla' align='center'>$presente</td>\n";
                    $j++;
                } else {
                    $HTML_alumnos_prov .= "<td class='textoTabla' align='center'><div style='background:#FFFF00'> ❓ </div></td>\n";
                }
            }
            $HTML_alumnos_prov .= "</tr>\n";
        }
        $HTML_alumnos_provisorios = $HTML_alumnos_prov;
    }
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<table cellpadding="4" cellspacing="0" border="0" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class="celdaFiltro" style="vertical-align: top;">
       Acciones:<br>
       <a href='<?php echo("$enlbase_sm=cursos_libro_clases_nueva_sesion&id_curso=$id_curso"); ?>' class='boton' id='sgu_fancybox'>🗓 Nueva Sesión</a> &nbsp;
       <!-- <a href='<?php echo("$enlbase_sm=cursos_libro_clases_lista_provisoria&id_curso=$id_curso"); ?>' class='boton' id='sgu_fancybox'>Lista Provisoria</a> &nbsp; -->
       <a href='#' onClick="parent.jQuery.fancybox.close();" class='boton'>❌ Cerrar</a>
  </td>
  </tr>
</table><table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
<tr><td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes del Curso</td></tr>
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
  <tr>
    <td class='celdaNombreAttr'>Horario:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($horario); ?></td>
  </tr>
</table>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' rowspan="2">ID</td>
    <td class='tituloTabla' rowspan="2">Estudiantes</td>
    <td class='tituloTabla' colspan="18">Sesiones (<?php echo(count($sesiones)); ?>)</td>
  </tr>
  <tr class='filaTituloTabla'>  
    <?php echo($HTML_sesiones); ?>
  </tr>
  <?php echo($HTML_alumnos_curso); ?>
  <tr>  
    <td class='celdaNombreAttr' colspan="2">Tasa de Asistencia:</td>
    <?php echo($HTML_tasa_presentes); ?>
  </tr>
  <tr class='filaTituloTabla'><td class='tituloTabla' colspan='2'>Lista Provisoria</td><td class='tituloTabla' colspan='18'>&nbsp;</td></tr>
  <?php echo($HTML_alumnos_provisorios); ?>  
</table>
<!-- Fin: <?php echo($modulo); ?> -->

