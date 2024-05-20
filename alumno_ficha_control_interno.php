<?php

$SQL_alumno = "SELECT va.id,va.nombre,va.rut,
                      CASE a.conc_nt_ies_pro WHEN true THEN 'Si' ELSE 'No' END AS conc_nt_ies_pro,
                      CASE a.prog_as_ies_pro WHEN true THEN 'Si' ELSE 'No' END AS prog_as_ies_pro,
                      CASE a.cert_nacimiento WHEN true THEN 'Si' ELSE 'No' END AS cert_nacimiento,
                      CASE a.copia_ced_iden WHEN true THEN 'Si' ELSE 'No' END AS copia_ced_iden,
                      CASE a.conc_notas_em  WHEN true THEN 'Si' ELSE 'No' END AS conc_notas_em,
                      CASE a.licencia_em WHEN true THEN 'Si' ELSE 'No' END AS licencia_em,                          
                      CASE a.boletin_psu WHEN true THEN 'Si' ELSE 'No' END AS boletin_psu,
                      CASE a.fotografias WHEN true THEN 'Si' ELSE 'No' END AS fotografias
               FROM vista_alumnos AS va
               LEFT JOIN alumnos AS a USING (id)
               WHERE va.id=$id_alumno;";                   
$alumno     = consulta_sql($SQL_alumno);
if (count($alumno) == 0) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
} else {
	extract($alumno[0]);
}

?>
<style>
.Si { color: #009900; font-weight: bold }
.No { color: #ff0000; font-weight: bold }
</style>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="100%">
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
    <td class='celdaValorAttr' colspan="3"><?php echo($alumno[0]['nombre']); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Documentación Obligatoria</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Fotocopia CI:</td>
    <td class='celdaValorAttr' width="50%" colspan="2"><span class="<?php echo($copia_ced_iden); ?>"><?php echo($copia_ced_iden); ?></span></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Cert. de Nacimiento:</td>
    <td class='celdaValorAttr' colspan="2"><span class="<?php echo($cert_nacimiento); ?>"><?php echo($cert_nacimiento); ?></span></td>
  </tr>
  <tr>    
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Licencia de EM:</td>
    <td class='celdaValorAttr' colspan="2"><span class="<?php echo($licencia_em); ?>"><?php echo($licencia_em); ?></span></td>
  </tr>
  <tr>    
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Concentración de Notas EM:</td>
    <td class='celdaValorAttr' colspan="2"><span class="<?php echo($conc_notas_em); ?>"><?php echo($conc_notas_em); ?></span></td>
    </td>
  </tr>
  <tr>    
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Fotografías:</td>
    <td class='celdaValorAttr' colspan="2"><span class="<?php echo($fotografias); ?>"><?php echo($fotografias); ?></span></td>
    </td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Documentación Opcional</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Boletín PAA/PSU:</td>
    <td class='celdaValorAttr' colspan="2"><span class="<?php echo($boletin_psu); ?>"><?php echo($boletin_psu); ?></span></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Documentación Admisión Extraordinaria</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Concentración de Notas:</td>
    <td class='celdaValorAttr' colspan="2"><span class="<?php echo($conc_nt_ies_pro); ?>"><?php echo($conc_nt_ies_pro); ?></spam></td>
  </tr>
  <tr>    
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Programas de Asignatura:</td>
    <td class='celdaValorAttr' style="font-weight: lighter;" colspan="2"><span class="<?php echo($prog_as_ies_pro); ?>"><?php echo($prog_as_ies_pro); ?></span></td>
  </tr>
</table>
