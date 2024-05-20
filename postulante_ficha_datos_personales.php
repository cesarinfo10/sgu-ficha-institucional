<?php

$SQL_postulante = "SELECT vp.id,vp.nombre,vp.rut,vp.genero,vp.fec_nac,vp.nacionalidad,pap.profesion,
                          CASE pap.est_civil WHEN 'S' THEN 'Soltero(a)' WHEN 'C' THEN 'Casado(a)' WHEN 'U' THEN 'Unido(a)'
                                             WHEN 'D' THEN 'Divorciado(a)' WHEN 'V' THEN 'Viudo(a)' END AS est_civil,
                          coalesce(vp.pasaporte,'**No corresponde**') AS pasaporte,
                          vp.direccion,vp.comuna,vp.region,vp.email,vp.telefono,vp.tel_movil,
                          at.nombre AS admision,pap.admision_subtipo,to_char(vp.fecha_post,'DD/MM/YYYY') AS fecha_post,
                          reg.nombre AS regimen,pap.estado_carpeta_doctos,
                          coalesce(c1.nombre,'** No ingresada **') AS carrera1,
                          CASE jornada1_post WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada1,  
                          coalesce(c2.nombre,'** No ingresada **') AS carrera2,
                          CASE jornada2_post WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada2,
                          coalesce(c3.nombre,'** No ingresada **') AS carrera3,
                          CASE jornada3_post WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada3,
                          coalesce(c4.nombre,'** No ingresada **') AS carrera4,
                          CASE jornada4_post WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada4,
                          coalesce(c5.nombre,'** No ingresada **') AS carrera5,
                          CASE jornada5_post WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada5,
                          coalesce(c6.nombre,'** No ingresada **') AS carrera6,
                          CASE jornada6_post WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada6,
                          r.nombre AS referencia,referencia_comentarios,pap.semestre_cohorte||'-'||pap.cohorte AS cohorte_post,vpe.estado,
                          obd.nombre AS origen_bd
                   FROM vista_pap AS vp
                   LEFT JOIN carreras AS c1 ON c1.id=vp.carrera1_post
                   LEFT JOIN carreras AS c2 ON c2.id=vp.carrera2_post
                   LEFT JOIN carreras AS c3 ON c3.id=vp.carrera3_post
                   LEFT JOIN pap ON pap.id=vp.id
                   LEFT JOIN regimenes_ AS reg ON reg.id=pap.regimen
                   LEFT JOIN admision_tipo AS at ON at.id=pap.admision
                   LEFT JOIN vista_pap_estados AS vpe ON vp.id=vpe.id
                   LEFT JOIN admision.referencias AS r ON r.id=pap.referencia
                   LEFT JOIN carreras AS c4 ON c4.id=pap.carrera4_post
                   LEFT JOIN carreras AS c5 ON c5.id=pap.carrera5_post
                   LEFT JOIN carreras AS c6 ON c6.id=pap.carrera6_post
                   LEFT JOIN admision.origenes_bd AS obd ON obd.id=pap.id_origen_bd
                   WHERE vp.id=$id_pap;";
$postulante     = consulta_sql($SQL_postulante);
if (count($postulante) == 0) {
	echo(js("location.href='principal.php?modulo=gestion_postulantes';"));
	exit;
}

?>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Postulante</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Género:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['genero']); ?></td>
    <td class='celdaNombreAttr'>Nacimiento:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['fec_nac']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Estado Civil:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['est_civil']); ?></td>
    <td class='celdaNombreAttr'>Profesion:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['profesion']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nacionalidad:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['nacionalidad']); ?></td>
    <td class='celdaNombreAttr'>Pasaporte:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['pasaporte']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['direccion']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Comuna:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['comuna']); ?></td>
    <td class='celdaNombreAttr'>Región:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['region']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tel. Fijo:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['telefono']); ?></td>
    <td class='celdaNombreAttr'>Tel. Móvil:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['tel_movil']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($postulante[0]['email']); ?></td>
  </tr>
  <tr> 
    <td class='celdaNombreAttr'>Referencia:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($postulante[0]['referencia']); ?><br><small><b>Comentarios:</b></small><?php echo($postulante[0]['referencia_comentarios']); ?></td>
  </tr> 
  <tr> 
    <td class='celdaNombreAttr'>Origen BD:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($postulante[0]['origen_bd']); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Postulación</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Admisión:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['admision']); ?><br><small><?php echo($postulante[0]['admision_subtipo']); ?></small></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['fecha_post']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['cohorte_post']); ?></td>
    <td class='celdaNombreAttr'>Carpeta Doctos.:</td>
    <!-- <td class='celdaValorAttr'><span class="<?php echo(implode(str_split(" ",$postulante[0]['estado'],1))); ?>"><?php echo($postulante[0]['estado']); ?></span></td> -->
    <td class='celdaValorAttr'><span class="<?php echo($postulante[0]['estado_carpeta_doctos']); ?>"><?php echo($postulante[0]['estado_carpeta_doctos']); ?></span></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Régimen:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['regimen']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>1ª Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['carrera1']); ?> <sub>Jornada</sub> <?php echo($postulante[0]['jornada1']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>2ª Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['carrera2']); ?> <sub>Jornada</sub> <?php echo($postulante[0]['jornada2']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>3ª Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['carrera3']); ?> <sub>Jornada</sub> <?php echo($postulante[0]['jornada3']); ?></td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'>4ª Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['carrera4']); ?> <sub>Jornada</sub> <?php echo($postulante[0]['jornada4']); ?></td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'>5ª Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['carrera5']); ?> <sub>Jornada</sub> <?php echo($postulante[0]['jornada5']); ?></td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'>6ª Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['carrera6']); ?> <sub>Jornada</sub> <?php echo($postulante[0]['jornada6']); ?></td>
  </tr>  
</table>

