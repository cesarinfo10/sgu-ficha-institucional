<?php
/*
$SQL_alumno = "SELECT a.id,a.rut,va.nombre,va.genero,va.fec_nac,va.nacionalidad,coalesce(va.pasaporte,'**No corresponde**') AS pasaporte,
                      va.direccion,va.comuna,va.region,va.telefono,coalesce(va.tel_movil,'** No se registra **') AS tel_movil,va.email,va.admision,
                      coalesce(va.semestre_cohorte,0)||'-'||va.cohorte AS cohorte,va.carrera,va.malla_actual,va.estado,va.id_malla_actual,
                      a.nombre_usuario,a.jornada,a.email AS email_personal
               FROM vista_alumnos AS va
               LEFT JOIN alumnos  AS a USING (id)
               WHERE a.id=$id_alumno;";*/

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
extract($alumno[0]);

$SQL_alumno_ca = "SELECT vac.id_curso, vac.id_pa, vac.id_pa_homo, vac.id_estado, 
                         CASE WHEN vac.id_curso IS NOT NULL THEN coalesce(vac.ano,'0')||'-'||coalesce(vac.semestre,'0')
                              WHEN vac.id_pa IS NOT NULL AND vac.id_pa_homo IS NULL THEN a.cohorte::text||'-0'
                              WHEN vac.id_pa IS NOT NULL AND vac.id_pa_homo IS NOT NULL
                                   THEN extract(YEAR FROM vac.fec_mod)::text||'-0'
                         END AS periodo, vac.asignatura, vac.s1, vac.nc, vac.s2, vac.recuperativa AS rec,vac.nf,vac.situacion,
                         vac.fecha_mod
                  FROM vista_alumnos_cursos AS vac
                  JOIN alumnos AS a ON a.id=vac.id_alumno
                  WHERE vac.id_alumno=$id_alumno 
                  ORDER BY periodo,vac.asignatura;";
$alumno_ca = consulta_sql($SQL_alumno_ca);

if (count($alumno_ca) > 0) {
	$_azul = "color: #000099";
	$_rojo = "color: #ff0000";
	$HTML_alumno_ca = "";
	$periodo_aux = $alumno_ca[0]['periodo'];
	
	for($x=0;$x<count($alumno_ca);$x++) {
		extract($alumno_ca[$x]);
		
		if ($periodo_aux <> $periodo) {
			$HTML_alumno_ca .= "<tr class='filaTabla'><td colspan='8' class='textoTabla'>&nbsp;</td></tr>";
		}
		$periodo_aux = $periodo;

		$estilo_s1 = $estilo_nc = $estilo_s2 = $estilo_rec = $estilo_nf = $estilo_sit = "color: #000000";
		
		if ($s1>=1 && $s1<4) { $estilo_s1 = $_rojo; } elseif ($s1>=4) { $estilo_s1 = $_azul; }   
		
		if ($nc>=1 && $nc<4) { $estilo_nc = $_rojo; } elseif ($nc>=4) { $estilo_nc = $_azul; }   
		
		if ($s2>=1 && $s2<4) { $estilo_s2 = $_rojo; } elseif ($s2>=4) { $estilo_s2 = $_azul; }   
		
		if ($rec>=1 && $rec<4) { $estilo_rec = $_rojo; } elseif ($rec>=4) { $estilo_rec = $_azul; }   
		
		if ($nf>=1 && $nf<4 ) { $estilo_nf = $_rojo; } elseif ($nf>=4 || $nf=="APC" || $nf=="APH" || $nf=="APECR") { $estilo_nf = $_azul; }   
		
		if ($situacion == "Reprobado") { $estilo_sit = $_rojo; } elseif ($situacion <> "Suspendido") { $estilo_sit = $_azul; }

		
		$HTML_alumno_ca .= "<tr class='filaTabla'>\n"
		                .  "  <td class='textoTabla'> $periodo</td>\n"
		                .  "  <td class='textoTabla' nowrap><div title='header=[Propiedades] fade=[on] body=[Fecha de Ingreso: $fecha_mod]'>$asignatura</div></td>\n"
		                .  "  <td class='textoTabla' style='$estilo_s1'> $s1</td>\n"
		                .  "  <td class='textoTabla' style='$estilo_nc'> $nc</td>\n"
		                .  "  <td class='textoTabla' style='$estilo_s2'> $s2</td>\n"
		                .  "  <td class='textoTabla' style='$estilo_rec'> $rec</td>\n"
		                .  "  <td class='textoTabla' style='$estilo_nf'> $nf</td>\n"
		                .  "  <td class='textoTabla' style='$estilo_sit' nowrap> $situacion</td>\n"
		                .  "</tr>\n";
	}
}

?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes Personales</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Código Interno:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($nombre); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Género:</td>
    <td class='celdaValorAttr'><?php echo($genero); ?></td>
    <td class='celdaNombreAttr'>Fecha de nacimiento:</td>
    <td class='celdaValorAttr'><?php echo($fec_nac); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nacionalidad:</td>
    <td class='celdaValorAttr'><?php echo($nacionalidad); ?></td>
    <td class='celdaNombreAttr'>Pasaporte:</td>
    <td class='celdaValorAttr'><?php echo($pasaporte); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes de Contacto</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($direccion); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Comuna:</td>
    <td class='celdaValorAttr'><?php echo($comuna_nombre); ?></td>
    <td class='celdaNombreAttr'>Región:</td>
    <td class='celdaValorAttr'><?php echo($region_nombre); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Telefóno fijo:</td>
    <td class='celdaValorAttr'><?php echo($telefono); ?></td>
    <td class='celdaNombreAttr'>Telefóno móvil:</td>
    <td class='celdaValorAttr'><?php echo($tel_movil); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail G-Suite:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($email_gsuite); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail Personal:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($email); ?></td>
  </tr>
<!-- <tr>
    <td class='celdaNombreAttr'>e-mail externo:</td>
    <td class='celdaValorAttr'><?php echo($email_externo); ?></td>
  </tr> -->
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes Curriculares</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Admisión:</td>
    <td class='celdaValorAttr'><?php echo($admision); ?></td>
    <td class='celdaNombreAttr'>Cohorte:</td>
    <td class='celdaValorAttr'><?php echo($cohorte); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera Actual:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($carrera."-".$jornada); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Año Malla Actual:</td>
    <td class='celdaValorAttr'><?php echo($malla_actual); ?></td>
    <td class='celdaNombreAttr'>Estado Académico:</td>
    <td class='celdaValorAttr' style="<?php echo($estilo_estado); ?>"><?php echo($estado); ?></td>
  </tr>  
</table>
<?php
	if ($_SESSION['tipo'] < 3) {
?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="100%" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class="tituloTabla" colspan="9">Rendimiento acad&eacute;mico del alumno</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Periodo</td>
    <td class='tituloTabla'>Asignatura</td>
    <td class='tituloTabla'>S1</td>
    <td class='tituloTabla'>NC</td>
    <td class='tituloTabla'>S2</td>
    <td class='tituloTabla'>Recup.</td>
    <td class='tituloTabla'>NF</td>
    <td class='tituloTabla'>Situaci&oacute;n</td>
  </tr>
  <?php echo($HTML_alumno_ca); ?>
</table>
<?php
	}
?>
