<?php

$SQL_alumno = "SELECT va.id,va.rut,va.nombre,va.genero,va.fec_nac,
                      va.nacionalidad,coalesce(va.pasaporte,'**No corresponde**') AS pasaporte,va.direccion,a.comuna,a.region,
                      va.comuna AS comuna_nombre,va.region AS region_nombre,
                      va.telefono,coalesce(va.tel_movil,'** No se registra **') AS tel_movil,
                      coalesce(va.semestre_cohorte,0) AS semestre_cohorte,va.cohorte,a.mes_cohorte,a.email,a.cohorte_reinc,a.semestre_cohorte_reinc,a.mes_cohorte_reinc,
                      trim(va.carrera) AS carrera_alias,va.malla_actual,va.estado,va.id_malla_actual,c.nombre AS carrera,
                      ae.nombre AS estado_tramite,CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
                      CASE WHEN a.moroso_financiero THEN 'Moroso' ELSE 'Al día' END AS moroso_financiero,
                      to_char(a.estado_fecha,'DD-tmMon-YYYY') AS estado_fecha,u.nombre_usuario AS estado_operador,
                      a.nombre_usuario||'@'||dominio_gsuite AS email_gsuite,
                      a.nombre_usuario||'@'||dominio AS email_inst,
                      at.nombre AS admision
               FROM vista_alumnos      AS va
               LEFT JOIN carreras      AS c  ON c.id=id_carrera
               LEFT JOIN regimenes_    AS r  ON r.id=c.regimen
               LEFT JOIN alumnos       AS a  ON a.id=va.id
               LEFT JOIN al_estados    AS ae ON ae.id=a.estado_tramite
               LEFT JOIN admision_tipo AS at ON at.id=a.admision
               LEFT JOIN pap                 ON pap.id=a.id_pap
               LEFT JOIN usuarios      AS u  ON u.id=a.estado_id_usuario
               WHERE va.id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) == 0) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
} else {
	extract($alumno[0]);
	$estilo_est_fin = "";
	if ($moroso_financiero == "Al día") { $estilo_est_fin = "si"; } else { $estilo_est_fin = "no"; }
	$moroso_financiero = "<span class='$estilo_est_fin'>$moroso_financiero</span>";
	if ($estado_fecha <> "") { $estado = "$estado <sup><i>($estado_fecha por $estado_operador)</i></sup>"; }
	if ($estado=="Moroso") { $estado = $estado_tramite; }
	$mat = consulta_sql("SELECT to_char(fecha,'dd-tmMon-YYYY') AS fecha FROM matriculas WHERE id_alumno=$id_alumno AND semestre=$SEMESTRE AND ano=$ANO");
	if (count($mat) > 0) { $mat = "<span class='si'>Si <small style='text-decoratio: none'>(desde el {$mat[0]['fecha']})</small></span>"; } else { $mat = "<span class='no'>No</span>"; }

	$aDatos_contacto = array("direccion","comuna","region","telefono","tel_movil","email");
	foreach($aDatos_contacto AS $campo) {
		$SQL_dat_con = "SELECT adc.$campo,to_char($campo"."_fecha,'DD-tmMon-YYYY') AS $campo"."_fecha,u1.nombre_usuario AS $campo"."_usuario
		                FROM alumnos_datos_contacto AS adc
		                LEFT JOIN usuarios AS u1 ON u1.id=adc.$campo"."_usuario
		                WHERE adc.id_alumno=$id_alumno AND adc.$campo IS NOT NULL
		                ORDER BY adc.$campo"."_fecha DESC LIMIT 1";
		$dat_con = consulta_sql($SQL_dat_con);
		if (count($dat_con) > 0) {
			if ($campo == "comuna" || $campo == "region") {
				$alumno[0][$campo."_nombre"] .= "<br><small><i>desde el {$dat_con[0][$campo.'_fecha']}, por {$dat_con[0][$campo.'_usuario']}</i></small>";
			} else {
				$alumno[0][$campo] .= "<br><small><i>desde el {$dat_con[0][$campo.'_fecha']}, por {$dat_con[0][$campo.'_usuario']}</i></small>";
			}
		} else {
			if ($campo == "comuna" || $campo == "region") {
				$alumno[0][$campo."_nombre"] .= "<br><small><i>desde la Matrícula, por Admisión</i></small>";
			} else {
				$alumno[0][$campo] .= "<br><small><i>desde la Matrícula, por Admisión</i></small>";
			}
		}
	}
}

if ($estado_tramite <> "") {
	echo(msje_js("Actualmente este alumno está trámite de obtener el estado de $estado_tramite"));
	$estado_tramite = "<sub><br>En trámite: <b>$estado_tramite</b></sub>";
}

if ($mes_cohorte <> "") { $mes_cohorte = $meses_palabra[$mes_cohorte-1]['nombre']; }

if (!empty($cohorte_reinc) || !empty($mes_cohorte_reinc) || !empty($semestre_cohorte_reinc)) { 
	$cohorte_comp_reinc = "$semestre_cohorte_reinc-$cohorte_reinc ({$meses_palabra[$mes_cohorte_reinc-1]['nombre']})";
}

?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td>
  </tr>
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
  <tr>
    <td class='celdaNombreAttr'>Género:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['genero']); ?></td>
    <td class='celdaNombreAttr' nowrap>Fecha Nac.:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['fec_nac']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nacionalidad:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['nacionalidad']); ?></td>
    <td class='celdaNombreAttr'>Pasaporte:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['pasaporte']); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      Antecedentes de Contacto
      <a href="<?php echo("$enlbase_sm=editar_alumno_datos_contacto&id_alumno=$id"); ?>" class="boton" id="sgu_fancybox_big">editar</a>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($alumno[0]['direccion']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Comuna:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['comuna_nombre']); ?></td>
    <td class='celdaNombreAttr'>Región:</td>
    <td class='celdaValorAttr' nowrap><?php echo($alumno[0]['region_nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tel. fijo:</td>
    <td class='celdaValorAttr'>+56 <?php echo($alumno[0]['telefono']); ?></td>
    <td class='celdaNombreAttr'>Tel. móvil:</td>
    <td class='celdaValorAttr'>+56 <?php echo($alumno[0]['tel_movil']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail Institucional:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($alumno[0]['email_inst']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cuenta GSuite:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($alumno[0]['email_gsuite']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail Personal:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($alumno[0]['email']); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Curriculares</td>
  </tr>
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
    <td class='celdaNombreAttr' style='text-align: left'>Estado<div style='text-align: right'>Académico:<br>Financiero:<br>Mat (<?php echo("$SEMESTRE-$ANO"); ?>):</div></td>
    <td class='celdaValorAttr' nowrap><?php echo("<br>".$estado."<br>".$moroso_financiero."<br>".$mat); ?></td>
  </tr>
</table>
