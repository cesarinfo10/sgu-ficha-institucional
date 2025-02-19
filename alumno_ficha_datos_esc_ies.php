<?php

$SQL_alumno = "SELECT id,nombre,rut,colegio,ano_egreso_col,promedio_col,
                      ano_psu,puntaje_psu,ies,carr_ies_pro,prom_nt_ies_pro
               FROM vista_alumnos
               WHERE id=$id_alumno;";                   
$alumno     = consulta_sql($SQL_alumno);
if (count($alumno) > 0) {
	extract($alumno[0]);
} else {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
}

?>

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
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Escolares del Alumno</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Colegio EM:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($alumno[0]['colegio']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Año de Egreso EM:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['ano_egreso_col']); ?></td>
    <td class='celdaNombreAttr'>Promedio EM:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['promedio_col']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Año PAA/PSU:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['ano_psu']); ?></td>
    <td class='celdaNombreAttr'>Puntaje PAA/PSU:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['puntaje_psu']); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Estudios Superiores anteriores del Alumno</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Inst. de Ed. Sup.:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($alumno[0]['ies']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['carr_ies_pro']); ?></td>
    <td class='celdaNombreAttr'>Promedio de Notas:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['prom_nt_ies_pro']); ?></td>
  </tr>
</table>

