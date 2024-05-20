<?php

$SQL_postulante = "SELECT vp.id,vp.nombre,vp.rut,
                          CASE vp.conc_nt_ies_pro WHEN true THEN 'Si' ELSE 'No' END AS conc_nt_ies_pro,
                          CASE vp.prog_as_ies_pro WHEN true THEN 'Si' ELSE 'No' END AS prog_as_ies_pro,
                          CASE vp.cert_nacimiento WHEN true THEN 'Si' ELSE 'No' END AS cert_nacimiento,
                          CASE vp.copia_ced_iden WHEN true THEN 'Si' ELSE 'No' END AS copia_ced_iden,
                          CASE vp.conc_notas_em  WHEN true THEN 'Si' ELSE 'No' END AS conc_notas_em,
                          CASE pap.conc_notas_em_comp_solic  WHEN true THEN 'Si' ELSE 'No' END AS conc_notas_em_comp_solic,
                          CASE vp.licencia_em WHEN true THEN 'Si' ELSE 'No' END AS licencia_em,
                          CASE pap.licencia_em_comp_solic WHEN true THEN 'Si' ELSE 'No' END AS licencia_em_comp_solic,
                          CASE vp.boletin_psu WHEN true THEN 'Si' ELSE 'No' END AS boletin_psu,
                          CASE pap.fotografias WHEN true THEN 'Si' ELSE 'No' END AS fotografias
                   FROM vista_pap AS vp
                   LEFT JOIN carreras AS c1 ON c1.id=vp.carrera1_post
                   LEFT JOIN carreras AS c2 ON c2.id=vp.carrera2_post
                   LEFT JOIN carreras AS c3 ON c3.id=vp.carrera3_post
                   LEFT JOIN pap ON pap.id=vp.id
                   WHERE vp.id=$id_pap;";                   
$postulante     = consulta_sql($SQL_postulante);
if (count($postulante) == 0) {
	echo(js("location.href='principal.php?modulo=gestion_postulantes';"));
	exit;
}

?>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="100%">
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
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Documentación Obligatoria</td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan='2' align='right'>
      Certificado de Nacimiento <sup>(ORIGINAL o Fotocopia Legalizada)</sup>: 
      <span class='<?php echo($postulante[0]['cert_nacimiento']); ?>'><?php echo($postulante[0]['cert_nacimiento']); ?></span>
      <br><br>
      Fotocopia de Cédula Nacional de Identidad: 
      <span class='<?php echo($postulante[0]['copia_ced_iden']); ?>'><?php echo($postulante[0]['copia_ced_iden']); ?></span>
      <br><br>
      2 Fotografías (Tamaño carnet con Nombre y RUT): 
      <span class='<?php echo($postulante[0]['fotografias']); ?>'><?php echo($postulante[0]['fotografias']); ?></span>
    </td>
    <td class='celdaValorAttr' colspan='2'>
            <div style='text-align: center'>Licencia de Enseñanza Media</div>
            ORIGINAL o Fotocopia Legalizada:
            <span class='<?php echo($postulante[0]['licencia_em']); ?>'><?php echo($postulante[0]['licencia_em']); ?></span>           
            &nbsp;
            Comprobante de Solicitud:
            <span class='<?php echo($postulante[0]['licencia_em_comp_solic']); ?>'><?php echo($postulante[0]['licencia_em_comp_solic']); ?></span>
            <hr>
            <div style='text-align: center'>Concentración de Notas</div>
            ORIGINAL o Fotocopia Legalizada:
            <span class="<?php echo($postulante[0]['conc_notas_em']); ?>"><?php echo($postulante[0]['conc_notas_em']); ?></span>
            &nbsp;
            Comprobante de Solicitud: 
            <span class="<?php echo($postulante[0]['conc_notas_em_comp_solic']); ?>"><?php echo($postulante[0]['conc_notas_em_comp_solic']); ?></span>
    </td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Documentación Opcional</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Boletín PAA/PSU:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['boletin_psu']); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Documentación Admisión Extraordinaria</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Concentración de Notas:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['conc_nt_ies_pro']); ?></td>
    <td class='celdaNombreAttr'>Programas de Asignatura:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['prog_as_ies_pro']); ?></td>
  </tr>
</table>
