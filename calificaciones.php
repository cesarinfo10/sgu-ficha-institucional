<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$ano      = $_REQUEST['ano'];
$semestre = $_REQUEST['semestre'];

if ($ano == "")      { $ano = $ANO; }
if ($semestre == "") { $semestre = $SEMESTRE; }

if ($ano <> $ANO || $semestre <> $SEMESTRE) {
	echo(msje_js("ATENCIÓN: Cambió el periodo de cursos siendo este distinto al actual ($SEMESTRE-$ANO). "
	            ."Considere la posibilidad que los cursos que ahora se mostrarán pueden estar cerrados y "
	            ."no son modificables las calificaciones "));
}

$es_profesor = false;
if ($_SESSION['tipo'] <> 3) {
	$id_profesor = "";
	if ($_REQUEST['nombre_usuario_profesor'] <> "" && $_REQUEST['suplantar'] <> "") {
		$nombre_usuario_profesor = $_REQUEST['nombre_usuario_profesor'];
		$SQL_profesor = "SELECT id,nombre||' '||apellido AS nombre FROM usuarios
		                 WHERE nombre_usuario = '$nombre_usuario_profesor' AND tipo = 3;";
		$profesor = consulta_sql($SQL_profesor);
		if (count($profesor) > 0) {
			$id_profesor     = $profesor[0]['id'];
			$nombre_profesor = $profesor[0]['nombre'];
			$es_profesor     = true;
		} else {
			echo(msje_js("No existe el nombre de usuario como profesor(a)"));
			echo(js("window.location='$enlbase=calificaciones';"));
		}
	}
} else {
	$nombre_profesor = nombre_real_usuario($_SESSION['usuario'],$_SESSION['tipo']);
	$id_profesor     = $_SESSION['id_usuario'];
	$es_profesor     = true;
	
	$SQL_profesor_pago = "SELECT 1 FROM finanzas.profesores_pago AS fpp WHERE id_profesor=$id_profesor";
	$profesor_pago = consulta_sql($SQL_profesor_pago);	
	if (count($profesor_pago) == 0) {
		$msje = "ATENCIÓN: No se encuentran registrados sus antecedentes de Pago de Servicios Docentes.\\n\\n"
		      . "Para ingresar esta información pinche en el botón \"Actualizar Antecedentes de Pago\".\\n\\n"
		      . "¿Desea hacerlo ahora?";
		$url_si = "$enlbase=ficha_profesor";
		$url_no = "#";
		echo(confirma_js($msje,$url_si,$url_no));
	}	
}

if ($es_profesor) {

	$SQL_cant_s1  = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne1)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))";
	$SQL_cant_nc  = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(nota_catedra)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))";
	$SQL_cant_s2  = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne2)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))";
	$SQL_cant_nf  = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(id_estado)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion)) and id_estado NOT IN (6,10)";
	$SQL_cal      = "SELECT CASE WHEN count(id)=count(materia) AND count(id)>0 THEN 'SI' ELSE 'NO' END AS cal FROM calendarizaciones WHERE id_curso=c.id";
	$SQL_fusiones = "SELECT char_comma_sum(vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura) AS asig_fusionadas FROM vista_cursos AS vc LEFT JOIN cursos AS ca USING (id) WHERE ca.id_fusion = c.id";

	$SQL_cursos = "SELECT c.id,cod_asignatura||'-'||c.seccion AS cod_asignatura, asignatura,c.semestre||'-'||c.ano AS periodo,
						  upper(u.apellido) as apellidos,initcap(u.nombre) AS nombres,sesion1,sesion2,sesion3,
						  cantidad_alumnos(c.id) AS cant_alumnos,cant_alumnos_asist(c.id) AS al_asist,c.cerrado,
						  ($SQL_cant_s1) AS s1, ($SQL_cant_nc) AS nc, ($SQL_cant_s2) AS s2, ($SQL_cant_nf) AS nf, ($SQL_cal) AS cal,
						  to_char(c.fec_ini,'TMDay DD-tmMon-YYYY') AS fec_ini,to_char(c.fec_fin,'TMDay DD-tmMon-YYYY') AS fec_fin,c.seccion,
						  ($SQL_fusiones) AS asig_fusionadas,c.tipo_clase
				   FROM vista_cursos AS vc
				   LEFT JOIN cursos AS c USING (id)
				   LEFT JOIN carreras AS car ON car.id=vc.id_carrera
				   LEFT JOIN usuarios AS u ON u.id=vc.id_profesor
				   WHERE id_fusion IS NULL AND c.id_profesor = '$id_profesor' AND c.semestre='$semestre' AND c.ano='$ano'
				   ORDER BY c.ano DESC, c.semestre DESC, cod_asignatura,c.seccion";
	$cursos = consulta_sql($SQL_cursos);

	$SQL_profesor = "SELECT vp.id,vp.rut,vp.nombre,vp.genero,vp.fec_nac,vp.direccion,vp.comuna,vp.region,
							vp.telefono,vp.tel_movil,vp.email,vp.email_personal,vp.escuela,vp.nacionalidad,
							vp.nombre_usuario,vp.grado_academico,to_char(u.grado_acad_fecha,'DD-MM-YYYY') as grado_acad_fecha,
							vp.grado_acad_universidad,vp.doc_fotocopia_ci,vp.doc_curriculum_vitae,vp.doc_certif_grado_acad,
							'Profesor(a) '||u.categorizacion||'(a)' as categorizacion,
							CASE WHEN u.activo THEN 'SI' ELSE 'NO' END AS activo
				   FROM vista_profesores AS vp
				   LEFT JOIN usuarios AS u USING (id)
				   WHERE vp.id=$id_profesor;";
	$profesor = consulta_sql($SQL_profesor);
	extract($profesor[0]);
	
	$select_semestres = "<select name='semestre' class='filtro' onChange='submitform();'>".select($semestres,$semestre)."</select>";
	$select_anos      = "<select name='ano' class='filtro' onChange='submitform();'>".select($anos,$ano)."</select>";
	
/*	
	$SQL_cant_s1    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne1)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso=c.id";
	$SQL_cant_nc    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(nota_catedra)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso=c.id";
	$SQL_cant_s2    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne2)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso=c.id";
	$SQL_cant_nf    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(id_estado)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso=c.id and id_estado NOT IN (6,10)";
	$SQL_cal         = "SELECT CASE WHEN count(id)=count(materia) AND count(id)>0 THEN 'SI' ELSE 'NO' END AS cal FROM calendarizaciones WHERE id_curso=c.id";

	$SQL_cursos = "SELECT c.id,cod_asignatura||'-'||c.seccion||' '||asignatura AS asignatura,
	                      c.semestre||'-'||c.ano AS periodo,cantidad_alumnos(c.id) AS cant_alumnos,cant_alumnos_asist(c.id) AS al_asist,
	                      coalesce(sesion1,'')||' '||coalesce(sesion2,'')||' '||coalesce(sesion3,'') as horario,
	                      ($SQL_cant_s1) AS s1, ($SQL_cant_nc) AS nc, ($SQL_cant_s2) AS s2, ($SQL_cant_nf) AS nf, ($SQL_cal) AS cal
	               FROM vista_cursos vc
                   LEFT JOIN cursos AS c USING (id)
	               WHERE c.id_profesor = '$id_profesor' AND NOT c.cerrado
	               ORDER BY c.ano,c.semestre,cod_asignatura;";
*/
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<div class="texto">
<?php if (!$es_profesor) { ?>
<form name="formulario" action="principal.php" method="get" onSubmit="return enblanco2('nombre_usuario');">
  <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
  <table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>
    <tr>
      <td class='celdaNombreAttr'>Nombre de usuario del Profesor:</td>
      <td class='celdaValorAttr'>
        <input type='text' size="12" name='nombre_usuario_profesor'>
        <script>formulario.nombre_usuario_profesor.focus();</script>
        <input type="submit" name="suplantar" value="Suplantar">
      </td>
    </tr>
  </table>
</form>
<?php	} else { ?>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="nombre_usuario_profesor" value="<?php echo($_REQUEST['nombre_usuario_profesor']); ?>">
<input type="hidden" name="suplantar" value="Suplantar">

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Profesor</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Código Interno:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($nombre); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Académicos del Profesor</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Categorización:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($categorizacion); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Grado Académico:</td>
    <td class='celdaValorAttr'><?php echo($grado_academico); ?></td>
    <td class='celdaNombreAttr' nowrap>Fecha de obtención:</td>
    <td class='celdaValorAttr'><?php echo($grado_acad_fecha); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Institución otorgante:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($grado_acad_universidad); ?></td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="12">Cursos del Periodo: <?php echo($select_semestres." - ".$select_anos); ?></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Nº Acta</td>
    <td class='tituloTabla'>Asignatura</td>
    <td class='tituloTabla'>A.I.</td>
    <td class='tituloTabla'>A.A.</td>
    <td class='tituloTabla'>H.R.</td>
    <td class='tituloTabla'>Periodo</td>
    <td class='tituloTabla'>Cal</td>
    <td class='tituloTabla'>S1</td>
    <td class='tituloTabla'>NC</td>
    <td class='tituloTabla'>S2</td>
    <td class='tituloTabla'>NF</td>
    <td class='tituloTabla'>Horario {sala}</td>
  </tr>
<?php
	$HTML_cursos = "";
	if (count($cursos) > 0) {
		$_verde   = "color: #009900; text-align: center";
		$_naranjo = "color: #FFA500; text-align: center";
		$_rojo    = "color: #ff0000; text-align: center";

		for ($x=0; $x<count($cursos); $x++) {
			extract($cursos[$x]);
			$cant_horas = total_horas_control_asist_2011($id);
			$est_s1 = $est_nc = $est_s2 = $est_rec = "color: #000000";
			
			/*
			if (strlen($asignatura)>30) { $asignatura = mb_substr($asignatura,0,30)."...";}
			if (strlen($profesor)>20)   { $profesor   = mb_substr($profesor,0,20)."...";}
			*/
			if ($asig_fusionadas <> "") { 
				$asig_fusionadas = explode(",",$asig_fusionadas);
				$asig_fusionadas = "<small>Fusionada con:<br>&nbsp;&nbsp;".implode("<br>&nbsp;&nbsp;",$asig_fusionadas)."</small>";
			}
			
			if ($s1==100) { $est_s1 = $_verde; } elseif ($s1==0) { $est_s1 = $_rojo; } else { $est_s1 = $_naranjo; }
			if ($nc==100) { $est_nc = $_verde; } elseif ($nc==0) { $est_nc = $_rojo; } else { $est_nc = $_naranjo; }
			if ($s2==100) { $est_s2 = $_verde; } elseif ($s2==0) { $est_s2 = $_rojo; } else { $est_s2 = $_naranjo; }
			if ($nf==100) { $est_nf = $_verde; } elseif ($nf==0) { $est_nf = $_rojo; } else { $est_nf = $_naranjo; }
			
			if ($seccion == 9) { $sesion1 = "<small>Comienzo: $fec_ini</small>"; $sesion2 = "<small>Término: $fec_fin</small>"; }
			
			$bgcolor_cerrado = "";
			if ($cerrado == "t") { $bgcolor_cerrado = "bgcolor='#C0FFC0'"; }
			
			$enl = "$enlbase=calificaciones_ver_curso&id_curso=$id";
			$enlace = "<a class='enlitem' href='$enl'>";
			echo("  <tr class='filaTabla' $bgcolor_cerrado onClick=\"window.location='$enl';\">\n");
			echo("    <td class='textoTabla'>$id</td>");
			echo("    <td class='textoTabla'><div>$cod_asignatura </div><div>$asignatura</div><div>$asig_fusionadas</div></td>");
			echo("    <td class='textoTabla' style='text-align: right'>$cant_alumnos</td>");
			echo("    <td class='textoTabla' style='text-align: right'>$al_asist</td>");
			echo("    <td class='textoTabla' style='text-align: right'>$cant_horas</td>");
			echo("    <td class='textoTabla' style='text-align: center'>$periodo<br><small>$tipo_clase</small></td>");
			echo("    <td class='textoTabla'><span class='$cal'>$cal</span></td>");
			echo("    <td class='textoTabla' style='$est_s1'><small>$s1%</small></td>");
			echo("    <td class='textoTabla' style='$est_nc'><small>$nc%</small></td>");
			echo("    <td class='textoTabla' style='$est_s2'><small>$s2%</small></td>");
			echo("    <td class='textoTabla' style='$est_nf'><small>$nf%</small></td>");
			echo("    <td class='textoTabla'><div>$sesion1 </div><div>$sesion2 </div><div>$sesion3</div></td>");
			echo("  </tr>");
		}
	} else {
		echo("<td class='textoTabla' colspan='13' align='center'><br>** No hay registros para los criterios de b&uacute;squeda/selección **<br><br></td>\n");
	}
	echo($HTML_cursos);
?>
</table>
</div><br>
<div class="texto">
  A.I.: Alumnos Inscritos<br>
  A.A.: Alumnos Asistentes (no se cuentan los suspendidos/retirados/abandonados)<br>
  H.R.: Horas Realizadas (asistencia del docente al curso)
  Cal.: Calendarización del curso<br>
  S1, NC, S2: Indican estado ingreso de estas calificaciones<br>
  NF: Indica estado de cierre de cursos (cálculo de Notas Finales y Situaciones)
</div>

</form>
<?php	} ?>
