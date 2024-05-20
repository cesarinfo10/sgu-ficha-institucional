<?php 

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];
if (!is_numeric($id_alumno)) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
}
$rut            = $_REQUEST['rut'];
$fecha          = $_REQUEST['fecha'];
$tipo           = $_REQUEST['tipo'];
$id_malla_nueva = $_REQUEST['id_malla_nueva'];

if (empty($fecha)) { $fecha = date("d-m-Y"); }
if (empty($tipo))  { $tipo  = "art21"; }

$SQL_alumno = "SELECT va.id,va.rut,va.nombre,
                      coalesce(va.semestre_cohorte,0) AS semestre_cohorte,va.cohorte,a.mes_cohorte,a.cohorte_reinc,a.semestre_cohorte_reinc,a.mes_cohorte_reinc,
                      trim(va.carrera) AS carrera_alias,va.malla_actual,va.estado,va.id_malla_actual,c.nombre AS carrera,
                      ae.nombre AS estado_tramite,CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
                      CASE WHEN a.moroso_financiero THEN 'Moroso' ELSE 'Al día' END AS moroso_financiero,a.id_pap,
                      at.nombre AS admision,a.malla_actual AS id_malla_actual,m.ano AS ano_malla_actual,a.carrera_actual AS id_carrera,c.id_malla_actual AS carrera_malla_actual
               FROM vista_alumnos AS va
               LEFT JOIN carreras AS c ON c.id=id_carrera
               LEFT JOIN alumnos AS a ON a.id=va.id
               LEFT JOIN al_estados AS ae ON ae.id=a.estado_tramite
               LEFT JOIN admision_tipo AS at ON at.id=a.admision
			   LEFT JOIN mallas AS m ON m.id=a.malla_actual
               LEFT JOIN pap ON pap.id=a.id_pap
               WHERE va.id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);
extract($alumno[0]);

$id_alumno_nuevo = null;
if ($id_malla_nueva > 0) {
	$alumno_nuevo = consulta_sql("SELECT id FROM alumnos WHERE id_pap=$id_pap AND malla_actual = $id_malla_nueva");
	if (count($alumno_nuevo) == 1) { $id_alumno_nuevo = $alumno_nuevo[0]['id']; }
}

if ($id_malla_actual == $carrera_malla_actual) {
	echo(msje_js("AVISO: El/la estudiante ya se encuentra en la malla Vigente de la carrera en que se está adscrito.\\n\\n"
	            ."Si lo que busca es generar una homologación hacia una malla de otra carrera, podrá continuar. En otro caso, "
				."consulte con Registro Académico."));
}

if ($mes_cohorte <> "") { $mes_cohorte = $meses_palabra[$mes_cohorte-1]['nombre']; }

if (!empty($cohorte_reinc) || !empty($mes_cohorte_reinc) || !empty($semestre_cohorte_reinc)) { 
	$cohorte_comp_reinc = "$semestre_cohorte_reinc-$cohorte_reinc ({$meses_palabra[$mes_cohorte_reinc-1]['nombre']})";
}

if (empty($id_malla_nueva)) {
	$malla_actual_carrera = consulta_sql("SELECT id_malla_actual FROM carreras WHERE id = $id_carrera");
	if (count($malla_actual_carrera) > 0) {
		$id_malla_nueva = $malla_actual_carrera[0]['id_malla_actual'];
	} else {
		echo(msje_js("ERROR: La carrera en la que se encuentra el alumno no tiene definida una malla vigente.\\n\\n"
		            ."No se puede continuar\\n\\n\\n"
		            ."NOTA: Para corregir esto, asigne una malla vigente en el módulo Gestión de Carreras"));
		echo(js("location.href='principal.php?modulo=ver_alumno&id_alumno=$id_alumno&rut=$rut';"));
		exit;
	}
}

if ($_REQUEST["guardar"] == 'Guardar y obtener Acta') {
	$prehomo = consulta_sql("SELECT id FROM prehomologaciones WHERE id_alumno = $id_alumno AND id_malla_nueva=$id_malla_nueva");
	if (count($prehomo) > 0) {
		$id_prehomo = $prehomo[0]['id'];
		$SQL_prehomo_upd_ins = "UPDATE prehomologaciones 
		                        SET id_malla_actual=$id_malla_actual,fecha='$fecha'::date,tipo='$tipo' 
		                        WHERE id_alumno=$id_alumno AND id_malla_nueva=$id_malla_nueva";
	} else {
		$SQL_prehomo_upd_ins = "INSERT INTO prehomologaciones (id_alumno,id_malla_actual,id_malla_nueva,fecha,tipo,id_creador)
		                        VALUES ($id_alumno,$id_malla_actual,$id_malla_nueva,'$fecha'::date,'$tipo',{$_SESSION['id_usuario']})";
	}
	if (consulta_dml($SQL_prehomo_upd_ins) > 0) {
		if (!($id_prehomo > 0)) {
			$id_prehomo = consulta_sql("SELECT last_value FROM prehomologaciones_id_seq");
			$id_prehomo = $id_prehomo[0]['last_value'];
		}
			
		$prehomo_det = consulta_sql("SELECT id_prehomo FROM prehomologaciones_detalle WHERE id_prehomo=$id_prehomo");
		if (count($prehomo_det) > 0) { consulta_dml("DELETE FROM prehomologaciones_detalle WHERE id_prehomo=$id_prehomo"); }
		$SQL_prehomo_det = "";
		foreach ($_REQUEST['id_ca'] AS $id_prog_asig => $id_ca) {
			if ($id_ca > 0) { $SQL_prehomo_det .= "INSERT INTO prehomologaciones_detalle VALUES ($id_prehomo,$id_prog_asig,$id_ca);"; }
		}
		if (consulta_dml($SQL_prehomo_det) > 0) {
			echo(js("window.open('emitir_acta_homologacion.php?id_alumno=$id_alumno&id_malla_nueva=$id_malla_nueva');")); 
		}
	}
}

$boton_aplicar_homo_disabled = "disabled";

$SQL_prehomo = "SELECT ph.*,to_char(fecha,'DD-MM-YYYY') AS fecha,to_char(fecha_creacion,'tmDay DD-tmMon-YYYY HH24:MI') AS fecha_creacion,
                       u.nombre||' '||u.apellido AS creador
                FROM prehomologaciones AS ph
                LEFT JOIN usuarios AS u ON u.id=id_creador
                WHERE id_alumno = $id_alumno AND id_malla_actual=$id_malla_actual AND id_malla_nueva=$id_malla_nueva";
$prehomo = consulta_sql($SQL_prehomo);
if (count($prehomo) > 0) {
	$id_prehomo     = $prehomo[0]['id'];
	$creador        = $prehomo[0]['creador'];
	$fecha_creacion = $prehomo[0]['fecha_creacion'];
	$prehomo_det    = consulta_sql("SELECT * FROM prehomologaciones_detalle phd WHERE id_prehomo = $id_prehomo");
	
	$fecha          = $prehomo[0]['fecha'];
	$tipo           = $prehomo[0]['tipo'];
	$id_malla_nueva = $prehomo[0]['id_malla_nueva']; 
	$boton_aplicar_homo_disabled = "";
	if ($_REQUEST["homologar"] == 'Aplicar Homologación') { 
		$id_alumno_aux = $id_alumno;
		if ($id_alumno_nuevo > 0) { $id_alumno = $id_alumno_nuevo; }
		$comentario_homo = "Aprobada según Acta de Homologación N° $id_prehomo";
		$SQL_ins_ca_homo = "INSERT INTO cargas_academicas (id_alumno,id_pa_homo,id_pa,homologada,id_estado,fecha_mod,valida,comentarios)
		                    SELECT $id_alumno,phd.id_prog_asig as id_pa_homo,vc.id_prog_asig as id_pa,true,3,'$fecha'::date,true,'$comentario_homo'
		                    FROM prehomologaciones_detalle AS phd 
		                    LEFT JOIN cargas_academicas AS ca ON ca.id=phd.id_ca 
		                    LEFT JOIN vista_cursos AS vc ON vc.id=ca.id_curso 
		                    WHERE id_prehomo=$id_prehomo";
		if (consulta_dml($SQL_ins_ca_homo) > 0) {
			echo(msje_js("Se ha aplicado con éxito la homologación."));
		} else {
			echo(msje_js("ERROR: No ha sido posible aplicar la homologación, posiblemente por que ya se aplicó anteriormente.\\n\\n "
			             ."De todas maneras informe este error al Departamento de Informática"));
		}
		$id_alumno = $id_alumno_aux;
		echo(js("top.location='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
		exit;	
	}
}

$SQL_detalle_malla = "SELECT id_prog_asig,cod_asignatura,asignatura,nivel,caracter
                      FROM vista_detalle_malla
                      WHERE id_malla=$id_malla_nueva";

$SQL_alumno_ca = "SELECT CASE WHEN ca.id_curso IS NOT NULL THEN c.id_prog_asig
                              WHEN ca.id_pa IS NOT NULL AND ca.convalidado THEN ca.id_pa
                              WHEN ca.id_pa_homo IS NOT NULL AND ca.homologada THEN ca.id_pa_homo
                              WHEN ca.id_pa IS NOT NULL AND ca.examen_con_rel THEN ca.id_pa
                         END AS id_prog_asig,
                         CASE WHEN ca.id_curso IS NOT NULL THEN c.semestre||'-'||c.ano
                              WHEN ca.id_pa IS NOT NULL AND ca.convalidado THEN '$semestre_cohorte-$cohorte'
                              WHEN ca.id_pa_homo IS NOT NULL AND ca.homologada THEN CASE WHEN extract(MONTH from ca.fecha_mod) <= 7 THEN 1 ELSE 2 END||'-'||extract(YEAR FROM ca.fecha_mod)
                              WHEN ca.id_pa IS NOT NULL AND ca.examen_con_rel THEN CASE WHEN extract(MONTH from ca.fecha_mod) <= 7 THEN 1 ELSE 2 END||'-'||extract(YEAR FROM ca.fecha_mod)
                         END AS periodo,
                         CASE WHEN ca.id_curso IS NOT NULL THEN coalesce(ca.nota_final::numeric(2,1)::text,'Cursando')||' '||coalesce(cae.nombre,'')
                              WHEN ca.id_pa IS NOT NULL AND ca.convalidado THEN 'APC'
                              WHEN ca.id_pa_homo IS NOT NULL AND ca.homologada THEN 'APH'
                              WHEN ca.id_pa IS NOT NULL AND ca.examen_con_rel THEN 'APECR'
                         END AS nf,ca.id_estado,ca.nota_final
                  FROM cargas_academicas AS ca
                  LEFT JOIN cursos AS c ON c.id=ca.id_curso
                  LEFT JOIN ca_estados AS cae ON cae.id=ca.id_estado
                  WHERE ca.id_alumno=$id_alumno AND ca.id_estado NOT IN (2,6) AND c.ano>=$ANO-10
                  ORDER BY periodo DESC";

$SQL_avance_malla = "SELECT dm.cod_asignatura||' '||dm.asignatura AS asignatura,dm.nivel,dm.caracter,dm.id_prog_asig,
                            char_comma_sum(coalesce(aca.nf,'No cursado')) AS estado,
                            char_comma_sum(aca.periodo) AS periodo,
                            char_comma_sum(text(aca.id_estado)) AS ids_estados
                     FROM ($SQL_detalle_malla) AS dm
                     LEFT JOIN ($SQL_alumno_ca) AS aca ON aca.id_prog_asig=dm.id_prog_asig
                     GROUP BY dm.cod_asignatura,dm.asignatura,dm.nivel,dm.caracter,dm.id_prog_asig
                     ORDER BY dm.nivel,asignatura;";
$avance_malla = consulta_sql($SQL_avance_malla);

/*
$SQL_asig_aprob = "SELECT ca.id AS id,c.semestre||'-'||c.ano||'/'||trim(m.alias_carrera)||m.ano||'/'||dm.cod_asignatura||' '||dm.asignatura||' (NF: '||ca.nota_final||')' AS nombre
                   FROM cargas_academicas        AS ca
                   LEFT JOIN cursos              AS c ON c.id=ca.id_curso
                   LEFT JOIN vista_detalle_malla AS dm USING (id_prog_asig)
                   LEFT JOIN vista_mallas        AS m ON m.id=dm.id_malla
                   WHERE ca.id_alumno IN (SELECT id FROM alumnos WHERE rut='$rut') AND ca.id_estado=1 AND dm.id_malla<>$id_malla_nueva AND cod_asignatura !~* 'SELLO'
                   ORDER BY dm.cod_asignatura";
$asig_aprob = consulta_sql($SQL_asig_aprob);
*/

$SQL_asig_aprob = "SELECT ca.id AS id,c.semestre||'-'||c.ano||'/'||trim(m.alias_carrera)||m.ano||'/'||dm.cod_asignatura||' '||dm.asignatura||' (NF: '||ca.nota_final||')' AS nombre
                   FROM cargas_academicas        AS ca
                   LEFT JOIN cursos              AS c ON c.id=ca.id_curso
                   LEFT JOIN vista_detalle_malla AS dm USING (id_prog_asig)
                   LEFT JOIN vista_mallas        AS m ON m.id=dm.id_malla
                   WHERE ca.id_alumno IN (SELECT id FROM alumnos WHERE rut='$rut') AND ca.id_estado=1 AND dm.id_malla<>$id_malla_nueva
                   ORDER BY dm.cod_asignatura";
$asig_aprob = consulta_sql($SQL_asig_aprob);

/*
$SQL_elect_aprob = "SELECT ca.id AS id,c.semestre||'-'||c.ano||'/'||trim(m.alias_carrera)||m.ano||'/'||dm.cod_asignatura||' '||dm.asignatura||' (NF: '||ca.nota_final||')' AS nombre
                    FROM cargas_academicas        AS ca
                    LEFT JOIN cursos              AS c ON c.id=ca.id_curso
                    LEFT JOIN vista_detalle_malla AS dm USING (id_prog_asig)
                    LEFT JOIN vista_mallas        AS m ON m.id=dm.id_malla
                    WHERE ca.id_alumno IN (SELECT id FROM alumnos WHERE rut='$rut') AND ca.id_estado=1 AND dm.id_malla<>$id_malla_nueva AND cod_asignatura ~* 'SELLO'
                    ORDER BY dm.cod_asignatura";
$elec_aprob = consulta_sql($SQL_elec_aprob);
*/
$HTML = "";
$campos_validar = array();

$nivel_aux = 0;
for($x=0;$x<count($avance_malla);$x++) {
	extract($avance_malla[$x]);

	if ($nivel_aux <> $nivel) {
		$HTML .= "<tr class='filaTabla'><td colspan='5' class='celdaNombreAttr' style='text-align: left'><i>{$NIVELES[$nivel-1]['nombre']} Semestre</i></td></tr>";
	}
	
	$nivel_aux = $nivel;
	$periodo = explode(",",$periodo);
	
	$id_ca = $id_pa_homo = "";
	if ($estado == "No cursado") {
		for ($z=0;$z<count($prehomo_det);$z++) {
			if ($id_prog_asig == $prehomo_det[$z]['id_prog_asig']) { $id_ca = $prehomo_det[$z]['id_ca']; }
		}
		$id_pa_homo = "<select name='id_ca[$id_prog_asig]' class='filtro' style='max-width: 500px'>"
		            . "  <option value='0'>-- Seleccione --</option>"
		            .    select($asig_aprob,$id_ca)
		            . "</select>";
//		if ($caracter == "Electiva") {
//			$id_pa_homo = "<select name='id_ca[$id_prog_asig]' class='filtro' style='max-width: 500px'>"
//			. "  <option value='0'>-- Seleccione --</option>"
//			.    select($elect_aprob,$id_ca)
//			. "</select>";
//		}
		$campos_validar[$x] = "id_ca[$id_prog_asig]";
	}
	$estado = explode(',',$estado);
	$ids_estados = explode(',',$ids_estados);
	for ($z=0;$z<count($estado);$z++) {
		if ($ids_estados[$z] == "2") { $color_estilo = "color: #ff0000"; }
		elseif ($ids_estados[$z] == "1") { $color_estilo = "color: #000099"; }
		$estado[$z] = "<div style='$color_estilo'>".trim($estado[$z])."</div>";
		$periodo[$z] = "<div style='$color_estilo'>".trim($periodo[$z])."</div>";
	}
	$estado = implode("",$estado);
	
	$color_estilo = "color: #000000";
	
	$periodo = implode("",$periodo);
	
	$HTML .= "<tr class='filaTabla'>\n"
		  .  "  <td class='textoTabla' nowrap> $asignatura</td>\n"
		  .  "  <td class='textoTabla' nowrap> $caracter</td>\n"
		  .  "  <td class='textoTabla' nowrap> $estado</td>\n"
		  .  "  <td class='textoTabla' nowrap> $periodo</td>\n"
		  .  "  <td class='textoTabla' nowrap> $id_pa_homo</td>\n"
		  .  "</tr>\n";
}
if (count($alumno) == 0) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
} else {
	extract($alumno[0]);
	$estilo_est_fin = "";
	if ($moroso_financiero == "Al día") { $estilo_est_fin = "si"; } else { $estilo_est_fin = "no"; }
	$moroso_financiero = "<span class='$estilo_est_fin'>$moroso_financiero</span>";
	if ($estado=="Moroso") { $estado = $estado_tramite; }
}

$SQL_mallas = "(SELECT 0 AS id,'-- Nuevas Mallas de la misma Carrera/Programa --' AS nombre)
               UNION ALL
               (SELECT id,alias_carrera||'/'||ano AS nombre FROM vista_mallas WHERE id_carrera=$id_carrera AND id <> $id_malla_actual AND ano>=$ano_malla_actual ORDER BY alias_carrera,ano DESC)
               UNION ALL
               (SELECT 0,'-- Mallas de otras Carreras/Programas --')
               UNION ALL
               (SELECT id,alias_carrera||'/'||ano AS nombre FROM vista_mallas WHERE id_carrera<>$id_carrera AND ano>=$ano_malla_actual ORDER BY alias_carrera,ano DESC)";
/*
$SQL_mallas = "SELECT id,alias||'/'||m.ano AS nombre,'Mallas Vigentes de la carrera' AS grupo 
               FROM carreras AS c 
			   LEFT JOIN regimenes AS r ON r.id=c.regimen
			   LEFT JOIN mallas AS m ON m.id=c.id_malla_actual
			   WHERE id = $id_carrera
			   ORDER BY c.regimen,c.nombre
			   UNION
			   SELECT id,alias||'/'||m.ano AS nombre,'Mallas Vigentes de otras carreras' AS grupo 
               FROM carreras AS c 
			   LEFT JOIN regimenes AS r ON r.id=c.regimen
			   LEFT JOIN mallas AS m ON m.id=c.id_malla_actual
			   WHERE id <> $id_carrera
			   ORDER BY c.regimen,c.nombre";
*/
$MALLAS     = consulta_sql($SQL_mallas);

?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name='formulario' method='post' onSubmit="return validar_homologacion('<?php echo(implode("','",$campos_validar)); ?>');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<input type="hidden" name="rut" value="<?php echo($rut); ?>">
<div style="margin-top: 5px">
  <input type="submit" name="guardar" value="Guardar y obtener Acta">
  <?php if ($_SESSION['tipo'] == 0) { ?>
    <input type="submit" name="homologar" value="Aplicar Homologación" <?php echo($boton_aplicar_homo_disabled); ?>>
  <?php } ?>
  <input type="button" name="cancelar" value="Cancelar" onClick="history.back();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#FFFFFF" style="margin-top: 5px">

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td></tr>

  <tr>
    <td class='celdaNombreAttr'>Código Interno:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['id']); ?></td>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['rut']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($alumno[0]['nombre']); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Curriculares</td></tr>

  <tr>
    <td class='celdaNombreAttr'>Admisión:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['admision']); ?></td>
    <td class='celdaNombreAttr'>Cohorte:<?php if (!empty($cohorte_comp_reinc)) { echo("<br>Reincorporación:"); } ?></td>
    <td class='celdaValorAttr'>
	  <?php 
	    echo("$semestre_cohorte-$cohorte ($mes_cohorte)");
	    if (!empty($cohorte_comp_reinc)) { echo("<br>$cohorte_comp_reinc"); }
	  ?>	  
	</td>
    </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera Actual:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['carrera'].' ('.$alumno[0]['carrera_alias'].')'); ?></td>
    <td class='celdaNombreAttr'>Jornada:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['jornada']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Año Malla Actual:</td>
    <td class='celdaValorAttr'>
      <a class='enlaces' href="<?php echo($enlbase.'=ver_malla&id_malla='.$alumno[0]['id_malla_actual']); ?>">
        <?php echo($alumno[0]['malla_actual']); ?>
      </a>
    </td>
    <td class='celdaNombreAttr' style='text-align: left'>Estado<div style='text-align: right'>Académico:<br>Financiero:</div></td>
    <td class='celdaValorAttr'><?php echo("<br>".$estado."<br>".$moroso_financiero); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Homologación</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Razón:</td>
    <td class='celdaValorAttr' colspan='3'>
      <input type='radio' name='tipo' value='reinc' id='reinc' <?php echo($tipo=='reinc') ? 'checked' : ''; ?>> <label for='reinc' style='vertical-align: top'>Reincorporación</label>
      &nbsp;&nbsp;&nbsp;
      <input type='radio' name='tipo' value='art21' id='art21' <?php echo($tipo=='art21') ? 'checked' : ''; ?>> <label for='art21' style='vertical-align: top'>Cambio voluntario</label>
    </td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><input type='input' name='fecha' value='<?php echo($fecha); ?>' id='fecha_homologacion' class='boton' size='10'></td>
    <td class='celdaNombreAttr'>Nueva Malla:</td>
    <td class='celdaValorAttr'><select name='id_malla_nueva' class='filtro' onChange='submitform();'><?php echo(select($MALLAS,$id_malla_nueva)); ?></select></td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'>Creador:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo("$creador el $fecha_creacion"); ?></td>
  </tr>  
</table>
<table cellpadding="2" cellspacing="1" bgcolor="#FFFFFF" border="0" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan='4'>Plan de Estudios (Malla) Nueva</td>
    <td class='tituloTabla' rowspan='2'>Homologar por<br><small>[asignaturas aprobadas en otra(s) malla(s)]</small></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Asignatura</td>
    <td class='tituloTabla'>Carácter</td>
    <td class='tituloTabla'>Situación</td>
    <td class='tituloTabla'>Periodo</td>
  </tr>
  <?php echo($HTML); ?>
  <tr class='filaTabla'>
    <td colspan='5' class='celdaNombreAttr'>
      <input type="submit" name="guardar" value="Guardar y obtener Acta">
      <?php if ($_SESSION['tipo'] == 0) { ?>
        <input type="submit" name="homologar" value="Aplicar Homologación" <?php echo($boton_aplicar_homo_disabled); ?>>
      <?php } ?>
      <input type="button" name="cancelar" value="Cancelar" onClick="history.back();">
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->
<script>
function validar_homologacion() {
	var campos=validar_homologacion.arguments, problemas=false, campo="", ramo1=0, ramo2=0;
	for (x=0; x<campos.length; x++) {
		for (y=0; y<campos.length; y++) {
			ramo1 = document.formulario.elements[campos[x]].value;
			ramo2 = document.formulario.elements[campos[y]].value;
			
			if (x != y && ramo1 == ramo2 && ramo1 > 0 && ramo2 > 0) {
				problemas = true;
				campo = campos[y];
				break;
			}
		}
		if (problemas) { break; }
	}
	if (problemas) {
		alert("ERROR: Existen dos o más asignaturas que se homologan por un mismo curso aprobado.\n\n"
		     +"Esto no es posible");
		document.formulario.elements[campo].focus();
		//document.formulario.elements[campo].select();
		return false;
	} else {
		return true;
	}
}
</script>
